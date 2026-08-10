<?php

namespace Database\Seeders;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommitmentSeeder extends Seeder
{
    public function run(): void
    {
        // Evita duplicar datos demo si el pipeline de deploy vuelve a
        // correr los seeders en cada release.
        if (Commitment::query()->exists()) {
            return;
        }

        $meetings = Meeting::all();
        $users = User::all();

        Commitment::factory()
            ->count(8)
            ->overdue()
            ->recycle([$meetings, $users])
            ->create();

        Commitment::factory()
            ->count(6)
            ->dueSoon()
            ->recycle([$meetings, $users])
            ->create();

        Commitment::factory()
            ->count(6)
            ->completed()
            ->recycle([$meetings, $users])
            ->create();

        Commitment::factory()
            ->count(10)
            ->recycle([$meetings, $users])
            ->create();

        // Simula progreso real para poblar el historial de auditoria
        // (regla obligatoria #3) y que "Ultima actividad" no quede vacia.
        Commitment::query()
            ->where('status', CommitmentStatus::Pendiente->value)
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->each(function (Commitment $commitment) {
                $commitment->update(['status' => CommitmentStatus::EnProgreso->value]);
            });
    }
}
