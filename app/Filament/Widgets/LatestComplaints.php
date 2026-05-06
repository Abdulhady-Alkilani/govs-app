<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Filament\Widgets\Widget;

class LatestComplaints extends Widget
{
    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.latest-complaints';

    protected int|string|array $columnSpan = 'full';

    public function getViewData(): array
    {
        return [
            'complaints' => Complaint::with(['citizen', 'type'])->latest()->take(5)->get(),
        ];
    }
}
