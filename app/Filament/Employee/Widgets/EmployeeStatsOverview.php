<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Complaint;
use App\Models\Inquiry;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $pendingComplaints = Complaint::where('assigned_to', $userId)->where('status', 'pending')->count();
        $pendingInquiries = Inquiry::where('assigned_to', $userId)->where('status', 'pending')->count();

        $completedComplaints = Complaint::where('assigned_to', $userId)->where('status', 'completed')->count();
        $completedInquiries = Inquiry::where('assigned_to', $userId)->where('status', 'completed')->count();

        return [
            Stat::make(__('Pending Requests'), $pendingComplaints + $pendingInquiries)
                ->icon('heroicon-o-clock')
                ->color('danger')
                ->description(__('Pending Requests')),
            Stat::make(__('Completed Requests'), $completedComplaints + $completedInquiries)
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->description(__('Completed Requests')),
        ];
    }
}
