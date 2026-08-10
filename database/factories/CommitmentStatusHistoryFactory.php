<?php

namespace Database\Factories;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\CommitmentStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommitmentStatusHistory>
 */
class CommitmentStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'commitment_id' => Commitment::factory(),
            'user_id' => User::factory(),
            'old_status' => null,
            'new_status' => $this->faker->randomElement(CommitmentStatus::cases())->value,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
