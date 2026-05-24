<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\ComplaintResource\Pages;
use App\Filament\Employee\Resources\ComplaintResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Complaint;
use App\Services\AiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationLabel(): string
    {
        return __('filament.complaint.nav');
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
        return parent::getEloquentQuery()->where('assigned_to', Auth::id());
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
                    ->label(__('filament.form.complaint_type'))
                    ->relationship('type', 'name')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament.form.description'))
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'pending' => __('filament.status.pending'),
                        'processing' => __('filament.status.processing'),
                        'completed' => __('filament.status.completed_f'),
                        'rejected' => __('filament.status.rejected_f'),
                    ])
                    ->required(),
                Forms\Components\Textarea::make('internal_notes')
                    ->label(__('filament.form.internal_notes'))
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
                    ->limit(40)
                    ->tooltip(fn(?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.col.created_at'))
                    ->dateTime()
                    ->sortable(),
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
                            ->required(),
                        Forms\Components\Textarea::make('official_reply')
                            ->label(__('الرد الرسمي (يُولّد تلقائياً)'))
                            ->placeholder(__('اضغط "توليد الرد" ثم عدّل النص إذا لزم الأمر'))
                            ->rows(5),
                    ])
                    ->action(function (Complaint $record, array $data): void {
                        $reply = !empty($data['official_reply']) ? $data['official_reply'] : null;

                        if (empty($reply) && !empty($data['employee_quick_note'])) {
                            $reply = \App\Services\AiService::generateOfficialReply($data['employee_quick_note']) ?? $data['employee_quick_note'];
                        }

                        $record->update([
                            'status' => $data['status'],
                            'internal_notes' => $reply,
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
                            ->action(function () {})
                            ->extraAttributes([
                                'x-on:click.prevent' => '
                                    const quickNote = $wire.mountedTableActionsData[0]?.employee_quick_note;
                                    if (!quickNote || quickNote.length < 5) {
                                        new FilamentNotification()
                                            .title("يرجى كتابة ملاحظة أولاً (5 أحرف على الأقل)")
                                            .danger()
                                            .send();
                                        return;
                                    }

                                    const btn = $event.target.closest("button");
                                    const originalText = btn.innerHTML;
                                    btn.innerHTML = "⏳ جاري التوليد...";
                                    btn.disabled = true;

                                    fetch("/ai/generate-reply", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "X-CSRF-TOKEN": document.querySelector("meta[name=csrf-token]")?.content || "",
                                            "Accept": "application/json",
                                        },
                                        body: JSON.stringify({ quick_note: quickNote }),
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            $wire.mountedTableActionsData[0].official_reply = data.reply;
                                            $wire.$refresh();
                                            new FilamentNotification()
                                                .title("تم توليد الرد بنجاح ✨")
                                                .success()
                                                .send();
                                        } else {
                                            new FilamentNotification()
                                                .title("فشل التوليد: " + (data.message || "خطأ غير معروف"))
                                                .danger()
                                                .send();
                                        }
                                    })
                                    .catch(err => {
                                        new FilamentNotification()
                                            .title("حدث خطأ في الاتصال")
                                            .danger()
                                            .send();
                                    })
                                    .finally(() => {
                                        btn.innerHTML = originalText;
                                        btn.disabled = false;
                                    });
                                ',
                            ]),
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
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
