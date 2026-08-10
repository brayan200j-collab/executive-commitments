<?php

namespace Tests\Unit;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitmentOverdueTest extends TestCase
{
    use RefreshDatabase;

    public function test_past_due_and_not_completed_is_overdue(): void
    {
        $commitment = Commitment::factory()->create([
            'due_date' => now()->subDay(),
            'status' => CommitmentStatus::Pendiente->value,
        ]);

        $this->assertTrue($commitment->refresh()->isOverdue);
    }

    public function test_past_due_but_completed_is_not_overdue(): void
    {
        $commitment = Commitment::factory()->create([
            'due_date' => now()->subDay(),
            'status' => CommitmentStatus::Cumplido->value,
        ]);

        $this->assertFalse($commitment->refresh()->isOverdue);
    }

    public function test_due_today_is_not_overdue(): void
    {
        $commitment = Commitment::factory()->create([
            'due_date' => now()->startOfDay(),
            'status' => CommitmentStatus::Pendiente->value,
        ]);

        $this->assertFalse($commitment->refresh()->isOverdue);
    }

    public function test_future_due_date_is_not_overdue(): void
    {
        $commitment = Commitment::factory()->create([
            'due_date' => now()->addWeek(),
            'status' => CommitmentStatus::Pendiente->value,
        ]);

        $this->assertFalse($commitment->refresh()->isOverdue);
    }

    public function test_overdue_scope_matches_the_accessor(): void
    {
        Commitment::factory()->create(['due_date' => now()->subDay(), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->subDay(), 'status' => CommitmentStatus::Cumplido->value]);
        Commitment::factory()->create(['due_date' => now()->addWeek(), 'status' => CommitmentStatus::Pendiente->value]);

        $overdueViaScope = Commitment::overdue()->pluck('id')->sort()->values();
        $overdueViaAccessor = Commitment::all()->filter->isOverdue->pluck('id')->sort()->values();

        $this->assertEquals($overdueViaAccessor, $overdueViaScope);
    }
}
