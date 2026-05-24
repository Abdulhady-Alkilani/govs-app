<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use App\Models\Complaint;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        return [
            Stat::make(__('Total Users'), User::count())
                ->icon('heroicon-o-users')
                ->color('info')
                ->description(__('Total Citizens')),
            Stat::make(__('Total Complaints'), Complaint::count())
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->description(__('Total Complaints')),
            Stat::make(__('Paid Bills'), Bill::where('status', 'paid')->count())
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->description(__('Paid Bills')),
        ];
    }
}
