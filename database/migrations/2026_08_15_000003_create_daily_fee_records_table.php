<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_fee_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->string('notes', 255)->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // admin user id
            $table->timestamps();

            // One record per student per day
            $table->unique(['student_id', 'date'], 'uq_daily_fee_student_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_fee_records');
    }
};
