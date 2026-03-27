<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('vehicle_plate');
            $table->timestamp('access_time');
            $table->string('access_type');
            $table->boolean('is_authorized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
