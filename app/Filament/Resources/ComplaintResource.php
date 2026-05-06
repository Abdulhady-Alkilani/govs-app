<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Filament\Resources\ComplaintResource\RelationManagers\AttachmentsRelationManager;
use App\Models\Complaint;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'الشكاوى والاستعلامات';

    protected static ?string $modelLabel = 'شكوى';

    protected static ?string $pluralModelLabel = 'الشكاوى';

    protected static ?string $navigationLabel = 'الشكاوى';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('الرقم')
                    ->sortable(),
                Tables\Columns\TextColumn::make('citizen.name')
                    ->label('المواطن')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type.name')
                    ->label('النوع')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتملة',
                        'rejected' => 'مرفوضة',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('المعالج')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتملة',
                        'rejected' => 'مرفوضة',
                    ]),
                SelectFilter::make('type_id')
                    ->label('النوع')
                    ->relationship('type', 'name')
                    ->preload(),
                SelectFilter::make('assigned_to')
                    ->label('المعالج')
                    ->relationship('assignee', 'name')
                    ->preload(),
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
                Forms\Components\Select::make('citizen_id')
                    ->label('المواطن')
                    ->options(User::whereHas('role', fn ($q) => $q->where('name', 'citizen'))->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\Select::make('type_id')
                    ->label('نوع الشكوى')
                    ->relationship('type', 'name')
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('assigned_to')
                    ->label('المعالج')
                    ->options(User::whereHas('role', fn ($q) => $q->where('name', 'employee'))->pluck('name', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'processing' => 'قيد المعالجة',
                        'completed' => 'مكتملة',
                        'rejected' => 'مرفوضة',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('description')
                    ->label('الوصف')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('internal_notes')
                    ->label('ملاحظات داخلية')
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
