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

    public static function getNavigationGroup(): ?string
    {
        return __('filament.nav.bills');
    }

    public static function getModelLabel(): string
    {
        return __('filament.bill.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.bill.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.bill.nav');
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
                Tables\Columns\TextColumn::make('bill_type')
                    ->label(__('filament.col.bill_type'))
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'water' => __('Water Bill'),
                        'electricity' => __('Electricity Bill'),
                        'telecom' => __('Telecom Bill'),
                        'property_tax' => __('Property Tax'),
                        'traffic_fine' => __('Traffic Fine'),
                        'late_fine' => __('Late Fine'),
                        'other' => __('Other'),
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('filament.col.amount'))
                    ->money('SYP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.col.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'unpaid' => __('filament.status.unpaid'),
                        'paid' => __('filament.status.paid'),
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('filament.col.due_date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('filament.col.payment_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label(__('filament.col.transaction_id'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'unpaid' => __('filament.status.unpaid'),
                        'paid' => __('filament.status.paid'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view_receipt')
                    ->label(__('filament.action.view_receipt'))
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->url(fn($record) => asset('storage/' . $record->payment_receipt_path))
                    ->openUrlInNewTab()
                    ->visible(fn($record) => $record->status === 'paid' && !empty($record->payment_receipt_path)),
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
                    ->label(__('filament.form.citizen'))
                    ->relationship('citizen', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('bill_type')
                    ->label(__('filament.col.bill_type'))
                    ->options([
                        'water' => __('Water Bill'),
                        'electricity' => __('Electricity Bill'),
                        'telecom' => __('Telecom Bill'),
                        'property_tax' => __('Property Tax'),
                        'traffic_fine' => __('Traffic Fine'),
                        'late_fine' => __('Late Fine'),
                        'other' => __('Other'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('filament.col.amount'))
                    ->numeric()
                    ->required()
                    ->prefix(__('SYP')),
                Forms\Components\Select::make('status')
                    ->label(__('filament.col.status'))
                    ->options([
                        'unpaid' => __('filament.status.unpaid'),
                        'paid' => __('filament.status.paid'),
                    ])
                    ->required()
                    ->default('unpaid'),
                Forms\Components\DatePicker::make('due_date')
                    ->label(__('filament.col.due_date'))
                    ->required(),
                Forms\Components\FileUpload::make('payment_receipt_path')
                    ->label(__('filament.form.payment_receipt'))
                    ->directory('receipts')
                    ->downloadable()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf']),
                Forms\Components\Textarea::make('payment_details')
                    ->label(__('filament.form.payment_details'))
                    ->columnSpanFull(),
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
