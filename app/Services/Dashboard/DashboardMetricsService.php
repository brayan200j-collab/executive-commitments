<?php

namespace App\Services\Dashboard;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use App\Enums\RiskStatus;
use App\Models\Commitment;
use App\Models\CommitmentStatusHistory;
use App\Models\Risk;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Unica fuente de las metricas del dashboard ejecutivo. Se aisla de los
 * Widgets de Filament para poder testearla sin arrancar el panel y para
 * reutilizarla si en el futuro se necesita, por ejemplo, un endpoint API.
 */
class DashboardMetricsService
{
    public function totalCommitments(): int
    {
        return Commitment::query()->count();
    }

    public function completedCommitments(): int
    {
        return Commitment::query()->where('status', CommitmentStatus::Cumplido->value)->count();
    }

    public function overdueCommitments(): int
    {
        return Commitment::query()->overdue()->count();
    }

    public function dueSoonCommitments(int $days = 7): int
    {
        return Commitment::query()->dueSoon($days)->count();
    }

    public function activeRisks(): int
    {
        return Risk::query()->where('status', RiskStatus::Activo->value)->count();
    }

    /**
     * @return BaseCollection<string, int> label => total
     */
    public function commitmentsByStatus(): BaseCollection
    {
        return collect(CommitmentStatus::cases())
            ->mapWithKeys(fn (CommitmentStatus $status) => [
                $status->getLabel() => Commitment::query()->where('status', $status->value)->count(),
            ]);
    }

    /**
     * @return BaseCollection<string, int> label => total
     */
    public function commitmentsByPriority(): BaseCollection
    {
        return collect(CommitmentPriority::cases())
            ->mapWithKeys(fn ($priority) => [
                $priority->getLabel() => Commitment::query()->where('priority', $priority->value)->count(),
            ]);
    }

    /**
     * @return Collection<int, CommitmentStatusHistory>
     */
    public function recentActivity(int $limit = 8): Collection
    {
        return CommitmentStatusHistory::query()
            ->with(['commitment', 'user'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
