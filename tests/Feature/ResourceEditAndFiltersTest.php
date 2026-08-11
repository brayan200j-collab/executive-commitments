<?php

namespace Tests\Feature;

use App\Enums\CommitmentStatus;
use App\Filament\Resources\Commitments\CommitmentResource;
use App\Filament\Resources\Commitments\Pages\EditCommitment;
use App\Filament\Resources\Commitments\Pages\ListCommitments;
use App\Filament\Resources\Commitments\RelationManagers\StatusHistoriesRelationManager;
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
            StatusHistoriesRelationManager::class,
            ['ownerRecord' => $commitment, 'pageClass' => EditCommitment::class]
        )->assertSuccessful();
    }

    public function test_overdue_and_due_soon_table_filters_apply_without_errors(): void
    {
        $user = User::factory()->create();
        Commitment::factory()->create(['due_date' => now()->subDay(), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addDays(2), 'status' => CommitmentStatus::Pendiente->value]);
        Commitment::factory()->create(['due_date' => now()->addMonth(), 'status' => CommitmentStatus::Pendiente->value]);

        $this->actingAs($user);

        Livewire::test(ListCommitments::class)
            ->filterTable('overdue')
            ->assertCountTableRecords(1)
            ->resetTableFilters()
            ->filterTable('due_soon')
            ->assertCountTableRecords(1);
    }

    /**
     * El listado debe quedar ordenado por codigo ascendente (COM-0001,
     * COM-0002...) y mantener ese orden al cambiar de pagina, sin
     * importar fecha limite, estado o prioridad de cada compromiso.
     */
    public function test_commitments_list_is_sorted_by_code_ascending_across_pages(): void
    {
        $user = User::factory()->create();

        // Fechas/estado en desorden a proposito: si el orden dependiera
        // de otra columna por accidente, este test lo detectaria.
        $commitments = Commitment::factory()
            ->count(15)
            ->sequence(
                ['due_date' => now()->addWeeks(3), 'status' => CommitmentStatus::Pendiente->value],
                ['due_date' => now()->subWeek(), 'status' => CommitmentStatus::Cumplido->value],
                ['due_date' => now()->addDay(), 'status' => CommitmentStatus::Bloqueado->value],
            )
            ->create();

        $orderedByCode = $commitments->sortBy('code')->values();

        $this->actingAs($user);

        Livewire::test(ListCommitments::class)
            ->assertCanSeeTableRecords($orderedByCode->take(10), inOrder: true)
            ->call('gotoPage', 2)
            ->assertCanSeeTableRecords($orderedByCode->slice(10, 5), inOrder: true);
    }
}
