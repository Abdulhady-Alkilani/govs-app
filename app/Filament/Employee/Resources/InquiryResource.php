<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\InquiryResource\Pages;
use App\Filament\Employee\Resources\InquiryResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Inquiry;
use App\Services\AiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    public static function getNavigationLabel(): string
    {
        return __('filament.inquiry.nav');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where(function ($query) {
            $query->where('assigned_to', Auth::id())
                  ->orWhereNull('assigned_to');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('citizen_id')
                    ->label(__('filament.form.citizen'))
                    ->relationship('citizen', 'name')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('type_id')
                    ->label(__('filament.form.inquiry_type'))
                    ->relationship('type', 'name')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'pending' => __('filament.status.pending'),
                        'processing' => __('filament.status.processing'),
                        'completed' => __('filament.status.completed_f'),
                        'rejected' => __('filament.status.rejected_f'),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('result_text')
                    ->label(__('filament.form.inquiry_result'))
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('result_file_path')
                    ->label(__('filament.form.result_file'))
                    ->directory('inquiry_results'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('filament.col.id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('citizen.name')
                    ->label(__('filament.col.citizen'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label(__('filament.col.type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.col.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('filament.status.pending'),
                        'processing' => __('filament.status.processing'),
                        'completed' => __('filament.status.completed_f'),
                        'rejected' => __('filament.status.rejected_f'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.col.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'pending' => __('filament.status.pending'),
                        'processing' => __('filament.status.processing'),
                        'completed' => __('filament.status.completed_f'),
                        'rejected' => __('filament.status.rejected_f'),
                    ])
                    ->default('pending'),
                SelectFilter::make('type_id')
                    ->label(__('filament.col.type'))
                    ->relationship('type', 'name')
                    ->preload()
                    ->searchable(),
                TernaryFilter::make('assigned_to_me')
                    ->label(__('filament.filter.assigned_to_me'))
                    ->placeholder(__('الكل'))
                    ->trueLabel(__('نعم'))
                    ->falseLabel(__('لا'))
                    ->queries(
                        fn(Builder $query): Builder => $query->where('assigned_to', Auth::id()),
                        fn(Builder $query): Builder => $query->whereNull('assigned_to'),
                        fn(Builder $query): Builder => $query,
                    ),
                Filter::make('created_at')
                    ->label(__('filament.filter.created_at'))
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('filament.filter.created_from')),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('filament.filter.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = __('filament.filter.created_from') . ': ' . $data['created_from'];
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = __('filament.filter.created_until') . ': ' . $data['created_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                // ===== الميزة 2: تغيير الحالة والرد بالذكاء الاصطناعي =====
                Tables\Actions\Action::make('changeStatusAndReply')
                    ->label(__('تغيير الحالة والرد'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->modalHeading(__('تغيير الحالة وتوليد رد'))
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label(__('filament.col.status'))
                            ->options([
                                'completed' => __('filament.status.completed_f'),
                                'rejected' => __('filament.status.rejected_f'),
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('employee_quick_note')
                            ->label(__('ملاحظة سريعة'))
                            ->placeholder(__('اكتب ملاحظة مختصرة وسيقوم الذكاء الاصطناعي بتحويلها لرد رسمي...'))
                            ->rows(3)
                            ->required()
                            ->live(),
                        Forms\Components\Textarea::make('official_reply')
                            ->label(__('الرد الرسمي (يُولّد تلقائياً)'))
                            ->placeholder(__('اضغط "توليد الرد" ثم عدّل النص إذا لزم الأمر'))
                            ->rows(5),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $reply = !empty($data['official_reply']) ? $data['official_reply'] : null;

                        if (empty($reply) && !empty($data['employee_quick_note'])) {
                            $reply = AiService::generateOfficialReply($data['employee_quick_note']) ?? $data['employee_quick_note'];
                        }

                        $record->update([
                            'status' => $data['status'],
                            'result_text' => $reply,
                        ]);

                        Notification::make()
                            ->title(__('تم تحديث الحالة والرد بنجاح'))
                            ->success()
                            ->send();
                    })
                    ->extraModalFooterActions([
                        Tables\Actions\Action::make('generateAiReply')
                            ->label('🪄 ' . __('توليد رد رسمي'))
                            ->color('warning')
                            ->action(function ($livewire) {
                                $data = $livewire->mountedTableActionsData[0] ?? [];
                                $quickNote = $data['employee_quick_note'] ?? '';

                                if (strlen($quickNote) < 5) {
                                    Notification::make()
                                        ->title(__('يرجى كتابة ملاحظة أولاً (5 أحرف على الأقل)'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $reply = AiService::generateOfficialReply($quickNote);

                                if (! $reply) {
                                    Notification::make()
                                        ->title(__('فشل توليد الرد. يرجى المحاولة مرة أخرى.'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $livewire->mountedTableActionsData[0]['official_reply'] = $reply;

                                Notification::make()
                                    ->title(__('تم توليد الرد بنجاح ✨'))
                                    ->success()
                                    ->send();
                            }),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInquiries::route('/'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
