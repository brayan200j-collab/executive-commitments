<?php

namespace Tests\Unit;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Services\ExecutiveSummary\LocalExecutiveSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalExecutiveSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_reports_no_data_when_empty(): void
    {
        $summary = app(LocalExecutiveSummaryService::class)->generate();

        $this->assertSame('Motor local (reglas sobre datos en tiempo real)', $summary->providerName);
        $this->assertStringContainsString('no hay compromisos', $summary->body);
    }

    public function test_summary_highlights_overdue_and_critical_commitments(): void
    {
        Commitment::factory()->create([
            'due_date' => now()->subWeek(),
            'status' => CommitmentStatus::Pendiente->value,
            'priority' => CommitmentPriority::Critica->value,
        ]);

        $summary = app(LocalExecutiveSummaryService::class)->generate();

        $this->assertStringContainsString('1 compromiso(s) vencido(s)', implode(' ', $summary->highlights));
        $this->assertStringContainsString('crítica', implode(' ', $summary->highlights));
    }
}
