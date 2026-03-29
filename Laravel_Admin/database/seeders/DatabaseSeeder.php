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
        // Comentado temporalmente ya que usamos la API
        // $this->call([
        //     UserSeeder::class,
        // ]);

        // Los datos ahora se crean directamente en la API
        // No se necesitan seeders locales por ahora
    }
}
