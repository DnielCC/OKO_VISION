<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'usuario', 'visitante'])->default('usuario')->after('password');
            $table->string('telefono')->nullable()->after('role');
            $table->string('direccion')->nullable()->after('telefono');
            $table->boolean('activo')->default(true)->after('direccion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'telefono', 'direccion', 'activo']);
        });
    }
};
