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
        Schema::create('employees', function (Blueprint $table) {
            $table->string('id', 11)->primary(); // Custom ID like '2024DEV244K'
            $table->string('emp_name', 100);
            $table->string('gender', 100);
            $table->string('emp_mail', 100)->unique();
            $table->integer('emp_number');
            $table->string('department', 100);
            $table->string('qr_code_path')->nullable(); // Path to QR code image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
