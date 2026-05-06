<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0 ;
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('إجمالي الشكاوى', Complaint::count())
                ->icon('heroicon-o-document-text')
                ->color('warning'),
            Stat::make('الفواتير المدفوعة', Bill::where('status', 'paid')->count())
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }
}
