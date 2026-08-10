<?php

namespace Database\Seeders;

use App\Enums\MeetingStatus;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    public function run(): void
    {
        // Evita duplicar datos demo si el pipeline de deploy vuelve a
        // correr los seeders en cada release.
        if (Meeting::query()->exists()) {
            return;
        }

        $users = User::all();

        Meeting::factory()
            ->count(8)
            ->recycle($users)
            ->create();

        Meeting::factory()->create([
            'title' => 'Comité directivo · Cierre de sprint',
            'date' => now()->subDays(3),
            'organization' => 'Infinity Group SAS',
            'responsible_id' => $users->random()->id,
            'status' => MeetingStatus::Realizada->value,
        ]);
    }
}
