<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * updateOrCreate() por email: si el pipeline de deploy vuelve a
     * correr los seeders (ej. en cada release), esto no debe fallar con
     * un UNIQUE constraint. El primer usuario es la credencial demo
     * documentada en el README.
     */
    public function run(): void
    {
        $demoUsers = [
            ['name' => 'Admin Demo', 'email' => 'admin@iaxel.test'],
            ['name' => 'Laura Gómez', 'email' => 'laura.gomez@iaxel.test'],
            ['name' => 'Carlos Restrepo', 'email' => 'carlos.restrepo@iaxel.test'],
            ['name' => 'Mariana Duque', 'email' => 'mariana.duque@iaxel.test'],
            ['name' => 'Andrés Salazar', 'email' => 'andres.salazar@iaxel.test'],
        ];

        foreach ($demoUsers as $demoUser) {
            User::query()->updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
