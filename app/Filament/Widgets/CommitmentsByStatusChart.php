<?php

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardMetricsService;
use Filament\Widgets\ChartWidget;

class CommitmentsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Compromisos por estado';

    protected function getData(): array
    {
        $byStatus = app(DashboardMetricsService::class)->commitmentsByStatus();

        return [
            'datasets' => [
                [
                    'label' => 'Compromisos',
                    'data' => $byStatus->values()->all(),
                    'backgroundColor' => ['#94a3b8', '#38bdf8', '#f43f5e', '#22c55e'],
                ],
            ],
            'labels' => $byStatus->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
