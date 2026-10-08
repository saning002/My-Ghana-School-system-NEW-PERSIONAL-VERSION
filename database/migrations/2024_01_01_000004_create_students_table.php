<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_id')->unique();
            $table->date('admission_date');
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->integer('level')->default(1);
            $table->enum('status', ['active', 'graduated', 'suspended'])->default('active');
            $table->foreignId('church_branch_id')->constrained('church_branches')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
