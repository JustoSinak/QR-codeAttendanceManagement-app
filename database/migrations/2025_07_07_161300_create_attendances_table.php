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
        Schema::create('attendances', function (Blueprint $table) {
            $table->string('id', 100)->primary(); // Custom ID
            $table->string('emp_name', 100);
            $table->date('date');
            $table->time('time_in', 6);
            $table->time('time_out', 6)->nullable();
            $table->integer('status')->default(0); // 0 = checked in, 1 = checked out
            $table->string('employee_id', 11)->nullable(); // Foreign key reference
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
