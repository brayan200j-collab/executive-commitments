<?php

namespace Database\Factories;

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
            'description' => $this->faker->sentence(14),
            'probability' => $this->faker->randomElement(RiskProbability::cases())->value,
            'impact' => $this->faker->randomElement(RiskImpact::cases())->value,
            // 'code' lo asigna RiskObserver::creating(), 'level' lo calcula RiskObserver::saving().
            'responsible_id' => User::factory(),
            'status' => $this->faker->randomElement(RiskStatus::cases())->value,
        ];
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
