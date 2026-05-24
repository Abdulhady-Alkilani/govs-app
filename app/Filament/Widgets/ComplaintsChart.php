<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ComplaintsChart extends ChartWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): string
    {
        return __('filament.widget.monthly_complaints');
    }

    protected function getData(): array
    {
        $data = $this->getComplaintsPerMonth();

        return [
            'datasets' => [
                [
                    'label' => __('filament.widget.incoming_complaints'),
                    'data' => $data->values()->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getComplaintsPerMonth()
    {
        $months = collect([]);
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('M Y');
            $months[$key] = 0;
        }

        $results = Complaint::select(
            DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('month')
            ->pluck('total', 'month');

        return $months->merge($results);
    }
}
