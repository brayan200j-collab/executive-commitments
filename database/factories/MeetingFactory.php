<?php

namespace Database\Factories;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Comité de seguimiento '.fake()->monthName().' '.fake()->year(),
            'date' => fake()->dateTimeBetween('-2 months', '+2 weeks'),
            'organization' => fake()->company(),
            'responsible_id' => User::factory(),
            'status' => fake()->randomElement(MeetingStatus::cases())->value,
        ];
    }
}
