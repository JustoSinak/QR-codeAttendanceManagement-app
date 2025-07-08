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
        // Add indexes to employees table for performance
        Schema::table('employees', function (Blueprint $table) {
            $table->index('emp_mail');
            $table->index('department');
            $table->index('employee_status');
            $table->index(['department', 'employee_status']);
            $table->index('hire_date');
        });

        // Add indexes to attendances table for performance
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['employee_id', 'date']);
            $table->index(['date', 'status']);
            $table->index('status');
            $table->index(['date', 'time_in']);
        });

        // Add indexes to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->index('adminname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['emp_mail']);
            $table->dropIndex(['department']);
            $table->dropIndex(['employee_status']);
            $table->dropIndex(['department', 'employee_status']);
            $table->dropIndex(['hire_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'date']);
            $table->dropIndex(['date', 'status']);
            $table->dropIndex(['status']);
            $table->dropIndex(['date', 'time_in']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->dropIndex(['adminname']);
        });
    }
};
