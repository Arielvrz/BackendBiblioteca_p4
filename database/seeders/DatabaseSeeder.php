<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            BookSeeder::class,
        ]);

        $bibliotecario = User::factory()->create([
            'name' => 'Bibliotecario',
            'email' => 'bibliotecario@test.com',
            'password' => bcrypt('password'),
        ]);
        $bibliotecario->assignRole('bibliotecario');

        $estudiante = User::factory()->create([
            'name' => 'Estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('password'),
        ]);
        $estudiante->assignRole('estudiante');

        $docente = User::factory()->create([
            'name' => 'Docente',
            'email' => 'docente@test.com',
            'password' => bcrypt('password'),
        ]);
        $docente->assignRole('docente');
    }
}
