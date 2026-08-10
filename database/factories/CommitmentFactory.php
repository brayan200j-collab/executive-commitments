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
            'description' => fake()->sentence(12),
            'responsible_id' => User::factory(),
            'due_date' => fake()->dateTimeBetween('-3 weeks', '+6 weeks'),
            'priority' => fake()->randomElement(CommitmentPriority::cases())->value,
            'status' => fake()->randomElement(CommitmentStatus::cases())->value,
            'progress_percentage' => fake()->numberBetween(0, 100),
            'evidence' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-4 weeks', '-1 days'),
            'status' => fake()->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
                CommitmentStatus::Bloqueado,
            ])->value,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('now', '+6 days'),
            'status' => fake()->randomElement([
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
