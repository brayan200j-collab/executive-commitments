<?php

namespace Tests\Unit;

use App\Enums\CommitmentStatus;
use App\Enums\RiskStatus;
use App\Models\Commitment;
use App\Models\Risk;
use App\Services\Dashboard\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_reflect_seeded_data(): void
    {
        Commitment::factory()->count(2)->create(['status' => CommitmentStatus::Cumplido->value]);
        Commitment::factory()->create(['due_date' => now()->subDay(), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addDays(3), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addMonth(), 'status' => CommitmentStatus::EnProgreso->value]);

        Risk::factory()->create(['status' => RiskStatus::Activo->value]);
        Risk::factory()->create(['status' => RiskStatus::Cerrado->value]);

        $metrics = app(DashboardMetricsService::class);

        $this->assertSame(5, $metrics->totalCommitments());
        $this->assertSame(2, $metrics->completedCommitments());
        $this->assertSame(1, $metrics->overdueCommitments());
        $this->assertSame(1, $metrics->dueSoonCommitments());
        $this->assertSame(1, $metrics->activeRisks());

        $byStatus = $metrics->commitmentsByStatus();
        $this->assertSame(2, $byStatus->get('Cumplido'));

        $this->assertCount(0, $metrics->recentActivity());
    }
}
