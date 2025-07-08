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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('photo_in')->nullable()->after('status'); // Photo taken during check-in
            $table->string('photo_out')->nullable()->after('photo_in'); // Photo taken during check-out
            $table->string('location_in')->nullable()->after('photo_out'); // GPS location for check-in
            $table->string('location_out')->nullable()->after('location_in'); // GPS location for check-out
            $table->json('device_info')->nullable()->after('location_out'); // Device information
            $table->decimal('overtime_hours', 5, 2)->default(0)->after('device_info'); // Overtime hours
            $table->integer('break_time')->default(0)->after('overtime_hours'); // Break time in minutes
            $table->text('notes')->nullable()->after('break_time'); // Additional notes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'photo_in',
                'photo_out',
                'location_in',
                'location_out',
                'device_info',
                'overtime_hours',
                'break_time',
                'notes'
            ]);
        });
    }
};
