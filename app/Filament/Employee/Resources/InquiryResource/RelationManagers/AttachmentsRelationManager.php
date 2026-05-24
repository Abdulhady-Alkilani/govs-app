<?php

namespace App\Filament\Employee\Resources\InquiryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $recordTitleAttribute = 'file_name';

    public static function getModelLabel(): string
    {
        return __('filament.attachment.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.attachment.plural');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_name')
                    ->label(__('filament.col.file_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_type')
                    ->label(__('filament.col.file_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.col.upload_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download')
                    ->label(__('filament.action.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label(__('filament.form.file'))
                    ->required()
                    ->directory('inquiry_attachments')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                    ->maxSize(10240),
                Forms\Components\TextInput::make('file_name')
                    ->label(__('filament.col.file_name'))
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('file_type')
                    ->label(__('filament.col.file_type'))
                    ->maxLength(255)
                    ->placeholder(__('filament.form.auto_filled')),
            ]);
    }
}
