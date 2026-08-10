<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * El primer usuario es la credencial demo documentada en el README.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@iaxel.test',
            'password' => bcrypt('password'),
        ]);

        User::factory()->createMany([
            ['name' => 'Laura Gómez', 'email' => 'laura.gomez@iaxel.test'],
            ['name' => 'Carlos Restrepo', 'email' => 'carlos.restrepo@iaxel.test'],
            ['name' => 'Mariana Duque', 'email' => 'mariana.duque@iaxel.test'],
            ['name' => 'Andrés Salazar', 'email' => 'andres.salazar@iaxel.test'],
        ]);
    }
}
