<?php

namespace Database\Factories;

use App\Enums\CommitmentPriority;
use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commitment>
 */
class CommitmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'description' => $this->faker->sentence(12),
            'responsible_id' => User::factory(),
            'due_date' => $this->faker->dateTimeBetween('-3 weeks', '+6 weeks'),
            'priority' => $this->faker->randomElement(CommitmentPriority::cases())->value,
            'status' => $this->faker->randomElement(CommitmentStatus::cases())->value,
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'evidence' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => $this->faker->dateTimeBetween('-4 weeks', '-1 days'),
            'status' => $this->faker->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
                CommitmentStatus::Bloqueado,
            ])->value,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn () => [
            'due_date' => $this->faker->dateTimeBetween('now', '+6 days'),
            'status' => $this->faker->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
            ])->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => CommitmentStatus::Cumplido->value,
            'progress_percentage' => 100,
        ]);
    }
}
