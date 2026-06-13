<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Complaint;
use App\Models\Inquiry;
use Filament\Widgets\Widget;

class EmployeeRequestsStatsTable extends Widget
{
    protected static ?int $sort = 3;

    protected static string $view = 'filament.employee.widgets.requests-stats-table';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        $userId = auth()->id();

        return [
            'complaintStatuses' => $this->getStatusCounts(Complaint::class, $userId),
            'inquiryStatuses' => $this->getStatusCounts(Inquiry::class, $userId),
        ];
    }

    protected function getStatusCounts(string $model, int $userId): array
    {
        $statuses = ['pending', 'processing', 'completed', 'rejected'];
        $counts = array_fill_keys($statuses, 0);

        $results = $model::where('assigned_to', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('total', 'status');

        foreach ($results as $status => $total) {
            $counts[$status] = $total;
        }

        $counts['total'] = array_sum($counts);

        return $counts;
    }
}
