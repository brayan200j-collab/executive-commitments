<?php

namespace Tests\Feature;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use App\Filament\Resources\Commitments\Pages\CreateCommitment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommitmentCreationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_commitment_assigns_a_code_and_logs_initial_audit_entry(): void
    {
        $admin = User::factory()->create();
        $meeting = Meeting::factory()->create();
        $responsible = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CreateCommitment::class)
            ->fillForm([
                'meeting_id' => $meeting->id,
                'description' => 'Entregar informe financiero mensual',
                'responsible_id' => $responsible->id,
                'due_date' => now()->addWeek()->toDateString(),
                'priority' => CommitmentPriority::Alta->value,
                'status' => CommitmentStatus::Pendiente->value,
                'progress_percentage' => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('commitments', [
            'code' => 'COM-0001',
            'description' => 'Entregar informe financiero mensual',
        ]);

        $this->assertDatabaseHas('commitment_status_histories', [
            'old_status' => null,
            'new_status' => CommitmentStatus::Pendiente->value,
        ]);
    }
}
