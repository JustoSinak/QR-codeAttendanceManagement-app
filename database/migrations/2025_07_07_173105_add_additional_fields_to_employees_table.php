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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('qr_code_path');
            $table->string('position')->nullable()->after('department');
            $table->date('hire_date')->nullable()->after('position');
            $table->enum('employee_status', ['active', 'inactive', 'terminated'])->default('active')->after('hire_date');
            $table->text('address')->nullable()->after('employee_status');
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('emergency_phone')->nullable()->after('emergency_contact');
            $table->decimal('salary', 10, 2)->nullable()->after('emergency_phone');
            $table->json('work_schedule')->nullable()->after('salary'); // Store work hours as JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo',
                'position',
                'hire_date',
                'employee_status',
                'address',
                'emergency_contact',
                'emergency_phone',
                'salary',
                'work_schedule'
            ]);
        });
    }
};
