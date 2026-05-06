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

    protected static ?string $navigationGroup = 'أنواع الشكاوى والاستعلامات';

    protected static ?string $modelLabel = 'نوع شكوى';

    protected static ?string $pluralModelLabel = 'أنواع الشكاوى';

    protected static ?string $navigationLabel = 'أنواع الشكاوى';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('complaints_count')
                    ->label('عدد الشكاوى')
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
                    ->label('الاسم')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('نشط')
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
