<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher_assignments', function (Blueprint $table) {
            $table->id();
            // The program (class) this teacher manages
            $table->foreignId('program_id')
                  ->constrained('programs')
                  ->cascadeOnDelete();
            // The lecturer who is the class teacher
            $table->foreignId('lecturer_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamps();

            // One class teacher per program (only one class teacher per class)
            $table->unique('program_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher_assignments');
    }
};
