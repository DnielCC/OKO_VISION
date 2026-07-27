<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'activo')) {
                $table->boolean('activo')->default(true)->after('password');
            }
            if (!Schema::hasColumn('usuarios', 'created_at')) {
                $table->timestamp('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (!Schema::hasColumn('usuarios', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            }
        });

        // Set todos los usuarios activos si estaban en NULL/0 por defecto tras agregar la columna
        DB::table('usuarios')->update(['activo' => true]);

        // Crear rol "Visitante" si aún no existe
        $existe = DB::table('roles')->where('nombre', 'Visitante')->exists();
        if (!$existe) {
            DB::table('roles')->insert(['nombre' => 'Visitante']);
        }
    }

    public function down(): void
    {
        // No se eliminan columnas ni el rol visitante en rollback (seguridad de datos).
        // Para un down estricto podríamos dropColumn y DELETE WHERE nombre='Visitante'.
    }
};
