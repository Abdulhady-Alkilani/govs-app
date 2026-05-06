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

    protected static ?string $navigationGroup = 'سجل النظام';

    protected static ?string $modelLabel = 'سجل';

    protected static ?string $pluralModelLabel = 'سجلات النظام';

    protected static ?string $navigationLabel = 'سجلات النظام';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action')
                    ->label('الإجراء')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('model_type')
                    ->label('نوع النموذج')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_id')
                    ->label('رقم النموذج')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
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
                    ->label('المستخدم')
                    ->content(fn ($record) => $record->user?->name),
                \Filament\Forms\Components\Placeholder::make('action')
                    ->label('الإجراء')
                    ->content(fn ($record) => $record->action),
                \Filament\Forms\Components\Placeholder::make('model_type')
                    ->label('نوع النموذج')
                    ->content(fn ($record) => $record->model_type),
                \Filament\Forms\Components\Placeholder::make('model_id')
                    ->label('رقم النموذج')
                    ->content(fn ($record) => $record->model_id),
                \Filament\Forms\Components\Placeholder::make('old_value')
                    ->label('القيمة القديمة')
                    ->content(fn ($record) => json_encode($record->old_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                \Filament\Forms\Components\Placeholder::make('new_value')
                    ->label('القيمة الجديدة')
                    ->content(fn ($record) => json_encode($record->new_value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                \Filament\Forms\Components\Placeholder::make('created_at')
                    ->label('التاريخ')
                    ->content(fn ($record) => $record->created_at?->format('Y-m-d H:i:s')),
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
