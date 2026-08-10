<?php

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardMetricsService;
use Filament\Widgets\ChartWidget;

class CommitmentsByPriorityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Compromisos por prioridad';

    protected function getData(): array
    {
        $byPriority = app(DashboardMetricsService::class)->commitmentsByPriority();

        return [
            'datasets' => [
                [
                    'label' => 'Compromisos',
                    'data' => $byPriority->values()->all(),
                    'backgroundColor' => ['#94a3b8', '#38bdf8', '#f59e0b', '#f43f5e'],
                ],
            ],
            'labels' => $byPriority->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
