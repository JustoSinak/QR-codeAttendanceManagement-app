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
        // Drop foreign key constraints temporarily
        DB::statement('ALTER TABLE attendances DROP FOREIGN KEY attendances_employee_id_foreign');
        DB::statement('ALTER TABLE employee_credentials DROP FOREIGN KEY employee_credentials_id_foreign');
        DB::statement('ALTER TABLE leave_requests DROP FOREIGN KEY leave_requests_employee_id_foreign');

        // Modify the column and add auto increment with primary key
        DB::statement('ALTER TABLE employees MODIFY id INT AUTO_INCREMENT PRIMARY KEY');

        // Recreate foreign key constraints
        DB::statement('ALTER TABLE attendances ADD CONSTRAINT attendances_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE employee_credentials ADD CONSTRAINT employee_credentials_id_foreign FOREIGN KEY (id) REFERENCES employees(id) ON DELETE CASCADE');
        DB::statement('ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_employee_id_foreign FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE');

        // Add qr_code_hash field if it doesn't exist
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'qr_code_hash')) {
                $table->string('qr_code_hash')->nullable()->unique()->after('qr_code_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('id', 11)->change();
        });
    }
};
