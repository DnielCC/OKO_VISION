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
        // Crear el administrador por defecto
        $admin = User::updateOrCreate(
            ['email' => 'admin@okovision.com'],
            [
                'name' => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('admin_password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'oko@admin.com'],
            [
                'name' => 'Oko Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                'email_verified_at' => now(),
            ]
        );

        // Crear el usuario de prueba
        $testUser = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Sembrar Vehículos
        \App\Models\Vehicle::updateOrCreate(
            ['plate' => 'ABC-123'],
            ['brand' => 'Toyota', 'model' => 'Corolla', 'color' => 'Gris', 'owner_id' => $testUser->id]
        );

        // Sembrar Alertas
        \App\Models\Alert::create([
            'title' => 'Acceso Denegado',
            'description' => 'Placa no reconocida: XYZ-999',
            'severity' => 'HIGH',
            'is_resolved' => false
        ]);

        \App\Models\Alert::create([
            'title' => 'Cámara Offline',
            'description' => 'La cámara principal no responde',
            'severity' => 'CRITICAL',
            'is_resolved' => false
        ]);

        // Sembrar Accesos
        \App\Models\AccessLog::create([
            'user_id' => $testUser->id,
            'vehicle_plate' => 'ABC-123',
            'access_time' => now(),
            'access_type' => 'ENTRY',
            'is_authorized' => true
        ]);
    }
}
