<?php

namespace Database\Seeders;

use App\Enums\RiskImpact;
use App\Enums\RiskProbability;
use App\Enums\RiskStatus;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Database\Seeder;

class RiskSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        // Cubre toda la matriz probabilidad x impacto al menos una vez.
        foreach (RiskProbability::cases() as $probability) {
            foreach (RiskImpact::cases() as $impact) {
                Risk::factory()
                    ->recycle($users)
                    ->create([
                        'probability' => $probability->value,
                        'impact' => $impact->value,
                    ]);
            }
        }

        Risk::factory()
            ->count(3)
            ->critical()
            ->recycle($users)
            ->create();

        Risk::factory()
            ->count(4)
            ->recycle($users)
            ->create(['status' => RiskStatus::Mitigado->value]);

        Risk::factory()
            ->count(2)
            ->recycle($users)
            ->create(['status' => RiskStatus::Cerrado->value]);
    }
}
