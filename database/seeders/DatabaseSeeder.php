<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * OJO: sin WithoutModelEvents. Los observers (codigo automatico,
     * nivel de riesgo, auditoria) son parte del comportamiento que este
     * seeder necesita ejercitar, no un efecto secundario a evitar.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MeetingSeeder::class,
            CommitmentSeeder::class,
            RiskSeeder::class,
        ]);
    }
}
