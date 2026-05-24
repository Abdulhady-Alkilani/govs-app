<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemLogResource\Pages;
use App\Models\SystemLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SystemLogResource extends Resource
{
    protected static ?string $model = SystemLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.system_logs');
    }

    public static function getModelLabel(): string
    {
        return __('filament.system_log.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.system_log.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.system_log.nav');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.col.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('filament.col.action'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label(__('filament.col.model_type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->label(__('filament.col.model_id'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.col.date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Placeholder::make('user_name')
                    ->label(__('filament.col.user'))
                    ->content(fn($record) => $record->user?->name),
                \Filament\Forms\Components\Placeholder::make('action')
                    ->label(__('filament.col.action'))
                    ->content(fn($record) => $record->action),
                \Filament\Forms\Components\Placeholder::make('model_type')
                    ->label(__('filament.col.model_type'))
                    ->content(fn($record) => $record->model_type),
                \Filament\Forms\Components\Placeholder::make('model_id')
                    ->label(__('filament.col.model_id'))
                    ->content(fn($record) => $record->model_id),
                \Filament\Forms\Components\Placeholder::make('old_value')
                    ->label(__('filament.form.old_value'))
                    ->content(fn($record) => json_encode($record->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                \Filament\Forms\Components\Placeholder::make('new_value')
                    ->label(__('filament.form.new_value'))
                    ->content(fn($record) => json_encode($record->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                \Filament\Forms\Components\Placeholder::make('created_at')
                    ->label(__('filament.col.date'))
                    ->content(fn($record) => $record->created_at?->format('Y-m-d H:i:s')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSystemLogs::route('/'),
            'view' => Pages\ViewSystemLog::route('/{record}'),
        ];
    }
}
