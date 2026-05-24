<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Filament\Resources\InquiryResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\AiService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.complaints_inquiries');
    }

    public static function getModelLabel(): string
    {
        return __('filament.inquiry.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.inquiry.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.inquiry.nav');
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
                            ->required(),
                        Forms\Components\Textarea::make('official_reply')
                            ->label(__('الرد الرسمي (يُولّد تلقائياً)'))
                            ->placeholder(__('اضغط "توليد الرد" ثم عدّل النص إذا لزم الأمر'))
                            ->rows(5),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $reply = !empty($data['official_reply']) ? $data['official_reply'] : null;

                        if (empty($reply) && !empty($data['employee_quick_note'])) {
                            $reply = \App\Services\AiService::generateOfficialReply($data['employee_quick_note']) ?? $data['employee_quick_note'];
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
                    ->relationship('citizen', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('type_id')
                    ->label(__('filament.form.inquiry_type'))
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
                Forms\Components\Textarea::make('result_text')
                    ->label(__('filament.form.result_text'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('result_file_path')
                    ->label(__('filament.form.result_file'))
                    ->directory('inquiry-results'),
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
            'create' => Pages\CreateInquiry::route('/create'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
