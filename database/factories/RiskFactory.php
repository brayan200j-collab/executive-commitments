<?php

namespace Database\Factories;

use App\Actions\Risks\GenerateRiskCodeAction;
use App\Enums\RiskImpact;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Risk>
 */
class RiskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(14),
            'probability' => fake()->randomElement(RiskProbability::cases())->value,
            'impact' => fake()->randomElement(RiskImpact::cases())->value,
            // 'level' lo calcula RiskObserver a partir de probability/impact.
            'responsible_id' => User::factory(),
            'status' => fake()->randomElement(RiskStatus::cases())->value,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Risk $risk) {
            $risk->code ??= app(GenerateRiskCodeAction::class)();
        });
    }

    public function critical(): static
    {
        return $this->state(fn () => [
            'probability' => RiskProbability::Alta->value,
            'impact' => RiskImpact::Alto->value,
            'status' => RiskStatus::Activo->value,
        ]);
    }
}
