<?php

namespace Tests\Feature;

use App\Enums\RiskImpact;
use App\Enums\RiskProbability;
use App\Filament\Resources\Risks\Pages\CreateRisk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RiskCreationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_high_probability_high_impact_risk_is_classified_as_critical(): void
    {
        $admin = User::factory()->create();
        $responsible = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(CreateRisk::class)
            ->fillForm([
                'description' => 'Proveedor clave podría incumplir el contrato',
                'probability' => RiskProbability::Alta->value,
                'impact' => RiskImpact::Alto->value,
                'responsible_id' => $responsible->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('risks', [
            'code' => 'RIS-0001',
            'level' => 'critico',
        ]);
    }
}
