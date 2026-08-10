<?php

namespace Database\Seeders;

use App\Enums\CommitmentStatus;
use App\Models\Commitment;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
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

        $users = User::all();

        // La fecha limite de un compromiso se calcula a partir de la
        // fecha de SU reunion de origen (nunca antes), para que los datos
        // demo sean coherentes: un compromiso no puede vencer antes de
        // que exista la reunion donde se adquirio.
        foreach (Meeting::all() as $meeting) {
            $commitmentsCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $commitmentsCount; $i++) {
                $dueDate = $meeting->date->copy()->addDays(fake()->numberBetween(3, 45));
                $status = $this->statusFor($dueDate);

                Commitment::factory()->create([
                    'meeting_id' => $meeting->id,
                    'responsible_id' => $users->random()->id,
                    'due_date' => $dueDate,
                    'status' => $status->value,
                    'progress_percentage' => $this->progressFor($status),
                ]);
            }
        }

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

    private function statusFor(Carbon $dueDate): CommitmentStatus
    {
        $today = Carbon::today();

        if ($dueDate->lt($today)) {
            // La fecha limite ya paso: casi siempre queda vencido, pero
            // a veces se alcanzo a cumplir a tiempo.
            return fake()->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
                CommitmentStatus::Bloqueado,
                CommitmentStatus::Cumplido,
            ]);
        }

        if ($dueDate->lte($today->copy()->addDays(7))) {
            // Vence pronto: todavia no deberia estar marcado como cumplido.
            return fake()->randomElement([
                CommitmentStatus::Pendiente,
                CommitmentStatus::EnProgreso,
            ]);
        }

        return fake()->randomElement(CommitmentStatus::cases());
    }

    private function progressFor(CommitmentStatus $status): int
    {
        return match ($status) {
            CommitmentStatus::Cumplido => 100,
            CommitmentStatus::Bloqueado => fake()->numberBetween(0, 40),
            CommitmentStatus::EnProgreso => fake()->numberBetween(20, 85),
            CommitmentStatus::Pendiente => fake()->numberBetween(0, 20),
        };
    }
}
