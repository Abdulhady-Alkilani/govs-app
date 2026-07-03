<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Resources\ComplaintResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Complaint;
use App\Models\User;
use App\Services\AiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.complaints_inquiries');
    }

    public static function getModelLabel(): string
    {
        return __('filament.complaint.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.complaint.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.complaint.nav');
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label(__('filament.col.type'))
                    ->searchable()
                    ->sortable(),
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
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai_priority')
                    ->label(__('أولوية AI'))
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'high' => '🔴 ' . __('عالية'),
                        'medium' => '🟡 ' . __('متوسطة'),
                        'low' => '⚪ ' . __('منخفضة'),
                        default => '-',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('ai_summary')
                    ->label(__('ملخص AI'))
                    ->limit(50)
                    ->tooltip(fn(?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label(__('filament.col.processor'))
                    ->searchable()
                    ->sortable(),
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
                    ]),
                SelectFilter::make('ai_priority')
                    ->label(__('أولوية AI'))
                    ->options([
                        'high' => __('عالية'),
                        'medium' => __('متوسطة'),
                        'low' => __('منخفضة'),
                    ]),
                SelectFilter::make('type_id')
                    ->label(__('filament.col.type'))
                    ->relationship('type', 'name')
                    ->preload(),
                SelectFilter::make('assigned_to')
                    ->label(__('filament.col.processor'))
                    ->relationship('assignee', 'name')
                    ->preload(),
            ])
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
                    ->action(function (Complaint $record, array $data): void {
                        try {
                            $reply = !empty($data['official_reply']) ? $data['official_reply'] : null;

                            if (empty($reply) && !empty($data['employee_quick_note'])) {
                                $reply = AiService::generateOfficialReply($data['employee_quick_note']) ?? $data['employee_quick_note'];
                            }

                            $record->update([
                                'status' => $data['status'],
                                'internal_notes' => $reply,
                            ]);

                            Notification::make()
                                ->title(__('تم تحديث الحالة والرد بنجاح'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title($e->getMessage())
                                ->danger()
                                ->send();
                        }
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

                                try {
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
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('citizen_id')
                    ->label(__('filament.form.citizen'))
                    ->options(User::whereHas('role', fn($q) => $q->where('name', 'citizen'))->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('type_id')
                    ->label(__('filament.form.complaint_type'))
                    ->relationship('type', 'name')
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('assigned_to')
                    ->label(__('filament.form.processor'))
                    ->options(User::whereHas('role', fn($q) => $q->where('name', 'employee'))->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'pending' => __('filament.status.pending'),
                        'processing' => __('filament.status.processing'),
                        'completed' => __('filament.status.completed_f'),
                        'rejected' => __('filament.status.rejected_f'),
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament.form.description'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('internal_notes')
                    ->label(__('filament.form.internal_notes'))
                    ->maxLength(65535)
                    ->columnSpanFull(),

                // ===== حقول AI (للقراءة فقط) =====
                Forms\Components\Section::make(__('تحليل الذكاء الاصطناعي'))
                    ->icon('heroicon-o-cpu-chip')
                    ->description(__('نتائج التحليل التلقائي بواسطة الذكاء الاصطناعي'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('ai_priority')
                            ->label(__('الأولوية'))
                            ->disabled()
                            ->dehydrated(false)
                            ->suffixIcon(fn(?string $state): ?string => match ($state) {
                                'high' => 'heroicon-o-exclamation-circle',
                                'medium' => 'heroicon-o-minus-circle',
                                'low' => 'heroicon-o-check-circle',
                                default => null,
                            }),
                        Forms\Components\Textarea::make('ai_summary')
                            ->label(__('الملخص'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
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
            'index' => Pages\ListComplaints::route('/'),
            'create' => Pages\CreateComplaint::route('/create'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
