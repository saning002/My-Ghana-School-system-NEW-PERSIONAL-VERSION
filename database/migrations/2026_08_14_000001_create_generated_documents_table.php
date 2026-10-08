<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                  // verification code in QR
            $table->string('document_type');                 // report_card, transcript, fee_statement, attendance_statement
            $table->string('title');                         // human label
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
            $table->unsignedBigInteger('generated_by');     // admin/lecturer user id
            $table->string('generated_by_name');
            $table->string('status')->default('valid');      // valid | revoked
            $table->json('meta')->nullable();                // extra info: program, attempt, filters etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
