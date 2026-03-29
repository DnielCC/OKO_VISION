<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador Principal',
            'email' => 'oko@admin.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'telefono' => '+1234567890',
            'direccion' => 'Oficina Principal',
            'activo' => true,
        ]);

        // Crear usuario normal
        User::create([
            'name' => 'Usuario Operador',
            'email' => 'usuario@oko.com',
            'password' => Hash::make('12345678'),
            'role' => 'usuario',
            'telefono' => '+1234567891',
            'direccion' => 'Departamento de Operaciones',
            'activo' => true,
        ]);

        // Crear usuario visitante
        User::create([
            'name' => 'Visitante Temporal',
            'email' => 'visitante@oko.com',
            'password' => Hash::make('12345678'),
            'role' => 'visitante',
            'telefono' => '+1234567892',
            'direccion' => 'Visita externa',
            'activo' => true,
        ]);

        // Crear usuarios adicionales para pruebas
        User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@oko.com',
            'password' => Hash::make('12345678'),
            'role' => 'usuario',
            'telefono' => '+1234567893',
            'direccion' => 'Calle Principal #123',
            'activo' => true,
        ]);

        User::create([
            'name' => 'María García',
            'email' => 'maria.garcia@oko.com',
            'password' => Hash::make('12345678'),
            'role' => 'usuario',
            'telefono' => '+1234567894',
            'direccion' => 'Avenida Secundaria #456',
            'activo' => false, // Inactivo para pruebas
        ]);

        $this->command->info('Usuarios creados exitosamente.');
    }
}
