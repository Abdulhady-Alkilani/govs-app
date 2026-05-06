<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'الفواتير';

    protected static ?string $modelLabel = 'فاتورة';

    protected static ?string $pluralModelLabel = 'الفواتير';

    protected static ?string $navigationLabel = 'الفواتير';

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
                Tables\Columns\TextColumn::make('bill_type')
                    ->label('نوع الفاتورة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'water' => 'مياه',
                        'electricity' => 'كهرباء',
                        'other' => 'أخرى',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'غير مدفوعة',
                        'paid' => 'مدفوعة',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('تاريخ الدفع')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('رقم المعاملة')
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'unpaid' => 'غير مدفوعة',
                        'paid' => 'مدفوعة',
                    ]),
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
                    ->relationship('citizen', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('bill_type')
                    ->label('نوع الفاتورة')
                    ->options([
                        'water' => 'مياه',
                        'electricity' => 'كهرباء',
                        'other' => 'أخرى',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('المبلغ')
                    ->numeric()
                    ->required()
                    ->prefix('ل.س'),
                Forms\Components\Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'unpaid' => 'غير مدفوعة',
                        'paid' => 'مدفوعة',
                    ])
                    ->required()
                    ->default('unpaid'),
                Forms\Components\DatePicker::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->required(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }
}
