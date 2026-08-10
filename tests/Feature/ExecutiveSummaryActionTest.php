<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Models\Commitment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExecutiveSummaryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_executive_summary_action_renders_without_errors(): void
    {
        $user = User::factory()->create();
        Commitment::factory()->count(3)->create();

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->callAction('generateExecutiveSummary')
            ->assertOk();
    }
}
