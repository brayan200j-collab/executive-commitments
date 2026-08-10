<?php

namespace Tests\Feature;

use App\Enums\CommitmentStatus;
use App\Filament\Resources\Commitments\CommitmentResource;
use App\Filament\Resources\Meetings\MeetingResource;
use App\Filament\Resources\Risks\RiskResource;
use App\Models\Commitment;
use App\Models\Meeting;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResourceEditAndFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_edit_pages_for_every_resource(): void
    {
        $user = User::factory()->create();
        $meeting = Meeting::factory()->create();
        $commitment = Commitment::factory()->create();
        $risk = Risk::factory()->create();

        $this->actingAs($user);

        $this->get(MeetingResource::getUrl('edit', ['record' => $meeting]))->assertSuccessful();
        $this->get(CommitmentResource::getUrl('edit', ['record' => $commitment]))->assertSuccessful();
        $this->get(RiskResource::getUrl('edit', ['record' => $risk]))->assertSuccessful();
    }

    public function test_commitment_status_history_relation_manager_renders_on_edit(): void
    {
        $user = User::factory()->create();
        $commitment = Commitment::factory()->create(['status' => CommitmentStatus::Pendiente->value]);
        $commitment->update(['status' => CommitmentStatus::EnProgreso->value]);

        $this->actingAs($user);

        Livewire::test(
            \App\Filament\Resources\Commitments\RelationManagers\StatusHistoriesRelationManager::class,
            ['ownerRecord' => $commitment, 'pageClass' => \App\Filament\Resources\Commitments\Pages\EditCommitment::class]
        )->assertSuccessful();
    }

    public function test_overdue_and_due_soon_table_filters_apply_without_errors(): void
    {
        $user = User::factory()->create();
        Commitment::factory()->create(['due_date' => now()->subDay(), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addDays(2), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addMonth(), 'status' => CommitmentStatus::Pendiente->value]);

        $this->actingAs($user);

        Livewire::test(\App\Filament\Resources\Commitments\Pages\ListCommitments::class)
            ->filterTable('overdue')
            ->assertCountTableRecords(1)
            ->resetTableFilters()
            ->filterTable('due_soon')
            ->assertCountTableRecords(1);
    }
}
