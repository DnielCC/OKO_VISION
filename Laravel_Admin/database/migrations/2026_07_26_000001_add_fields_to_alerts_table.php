<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->string('vehicle_plate')->nullable()->after('description');
            $table->string('vehicle_owner')->nullable()->after('vehicle_plate');
            $table->string('location')->nullable()->after('vehicle_owner');
            $table->string('camera')->nullable()->after('location');
            $table->string('status', 32)->default('PENDING')->after('is_resolved');
            $table->timestamp('resolved_at')->nullable()->after('status');
            $table->text('image_url')->nullable()->after('resolved_at');
            $table->text('notes')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_plate',
                'vehicle_owner',
                'location',
                'camera',
                'status',
                'resolved_at',
                'image_url',
                'notes',
            ]);
        });
    }
};
