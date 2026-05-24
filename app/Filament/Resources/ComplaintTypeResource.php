<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintTypeResource\Pages;
use App\Models\ComplaintType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ComplaintTypeResource extends Resource
{
    protected static ?string $model = ComplaintType::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.types');
    }

    public static function getModelLabel(): string
    {
        return __('filament.complaint_type.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.complaint_type.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.complaint_type.nav');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.col.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('filament.col.description'))
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.col.active'))
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('complaints_count')
                    ->label(__('filament.col.complaints_count'))
                    ->counts('complaints')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
                Forms\Components\TextInput::make('name')
                    ->label(__('filament.col.name'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label(__('filament.col.description'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label(__('filament.col.active'))
                    ->default(true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaintTypes::route('/'),
            'create' => Pages\CreateComplaintType::route('/create'),
            'edit' => Pages\EditComplaintType::route('/{record}/edit'),
        ];
    }
}
