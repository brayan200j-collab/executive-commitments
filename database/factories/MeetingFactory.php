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
            'title' => 'Comité de seguimiento '.$this->faker->monthName().' '.$this->faker->year(),
            'date' => $this->faker->dateTimeBetween('-2 months', '+2 weeks'),
            'organization' => $this->faker->company(),
            'responsible_id' => User::factory(),
            'status' => $this->faker->randomElement(MeetingStatus::cases())->value,
        ];
    }
}
