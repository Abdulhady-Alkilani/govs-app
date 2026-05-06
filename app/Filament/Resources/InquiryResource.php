<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'الشكاوى والاستعلامات';

    protected static ?string $modelLabel = 'استعلام';

    protected static ?string $pluralModelLabel = 'الاستعلامات';

    protected static ?string $navigationLabel = 'الاستعلامات';

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
                        'completed' => 'مكتمل',
                        'rejected' => 'مرفوض',
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
                        'completed' => 'مكتمل',
                        'rejected' => 'مرفوض',
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
                    ->relationship('citizen', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Forms\Components\Select::make('type_id')
                    ->label('نوع الاستعلام')
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
                        'completed' => 'مكتمل',
                        'rejected' => 'مرفوض',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('result_text')
                    ->label('نص النتيجة')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('result_file_path')
                    ->label('ملف النتيجة')
                    ->directory('inquiry-results'),
            ]);
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
