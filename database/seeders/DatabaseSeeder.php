<?php

namespace Database\Seeders;

use App\Enums\AppRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'email' => 'decano@uleam.edu.ec',
            'nombres' => 'Decano',
            'apellidos' => 'Facultad',
            'rol' => AppRole::Decano,
        ]);

        User::factory()->create([
            'email' => 'secretaria@uleam.edu.ec',
            'nombres' => 'Secretaria',
            'apellidos' => 'Académica',
            'rol' => AppRole::Secretaria,
        ]);

        User::factory()->create([
            'email' => 'docente@uleam.edu.ec',
            'nombres' => 'Docente',
            'apellidos' => 'Ejemplo',
            'rol' => AppRole::Docente,
        ]);
    }
}
