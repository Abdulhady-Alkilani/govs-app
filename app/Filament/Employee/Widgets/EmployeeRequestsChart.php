<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Complaint;
use App\Models\Inquiry;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeRequestsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('إحصائيات الطلبات الشهرية');
    }

    protected function getData(): array
    {
        $userId = auth()->id();

        $complaints = $this->getMonthlyTotals(Complaint::class, $userId);
        $inquiries = $this->getMonthlyTotals(Inquiry::class, $userId);

        return [
            'datasets' => [
                [
                    'label' => __('الشكاوى'),
                    'data' => $complaints->values()->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                ],
                [
                    'label' => __('الاستفسارات'),
                    'data' => $inquiries->values()->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.7)',
                ],
            ],
            'labels' => $complaints->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getMonthlyTotals(string $model, int $userId)
    {
        $months = collect([]);
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('M Y');
            $months[$key] = 0;
        }

        $results = $model::select(
            DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
            DB::raw('COUNT(*) as total')
        )
            ->where('assigned_to', $userId)
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        return $months->merge($results);
    }
}
