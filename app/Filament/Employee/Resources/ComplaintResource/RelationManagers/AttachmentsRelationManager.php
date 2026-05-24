<?php

namespace App\Filament\Employee\Resources\ComplaintResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static bool $canCreate = false;

    protected static bool $canEdit = false;

    protected static bool $canDelete = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('file_path')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_path')
                    ->label(__('filament.col.file_path')),
                Tables\Columns\TextColumn::make('file_type')
                    ->label(__('filament.col.file_type')),
                // ===== الميزة 6: عرض حالة التحقق بالذكاء الاصطناعي =====
                Tables\Columns\IconColumn::make('is_ai_verified')
                    ->label(__('تحقق AI'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn($record): string => match(true) {
                        $record->is_ai_verified === true => __('✅ تم التحقق آلياً - وثيقة صالحة'),
                        $record->is_ai_verified === false => __('❌ لم يتم التحقق - صورة غير مناسبة'),
                        default => __('⏳ لم يتم الفحص بعد'),
                    }),
                Tables\Columns\TextColumn::make('ai_ocr_text')
                    ->label(__('النص المستخرج'))
                    ->limit(30)
                    ->tooltip(fn(?string $state): ?string => $state)
                    ->placeholder(__('لا يوجد')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.col.upload_date'))
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label(__('filament.action.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
            ]);
    }
}
