<?php

namespace App\Filament\Widgets;

use App\Services\Dashboard\DashboardMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommitmentsStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $metrics = app(DashboardMetricsService::class);

        $total = $metrics->totalCommitments();
        $completed = $metrics->completedCommitments();
        $overdue = $metrics->overdueCommitments();
        $dueSoon = $metrics->dueSoonCommitments();
        $activeRisks = $metrics->activeRisks();

        return [
            Stat::make('Total de compromisos', $total)
                ->icon('heroicon-o-clipboard-document-list')
                ->color('gray'),
            Stat::make('Compromisos cumplidos', $completed)
                ->description($total > 0 ? round(($completed / $total) * 100).'% del total' : 'Sin registros')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Compromisos vencidos', $overdue)
                ->description('Superaron su fecha límite sin cumplirse')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($overdue > 0 ? 'danger' : 'gray'),
            Stat::make('Próximos a vencer', $dueSoon)
                ->description('En los próximos 7 días')
                ->icon('heroicon-o-clock')
                ->color($dueSoon > 0 ? 'warning' : 'gray'),
            Stat::make('Riesgos activos', $activeRisks)
                ->icon('heroicon-o-shield-exclamation')
                ->color($activeRisks > 0 ? 'danger' : 'gray'),
        ];
    }
}
