<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('per_student_invoices', function (Blueprint $table) {
            $table->id();

            // Which school and which term
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // The academic period this invoice covers (nullable = current term)
            $table->unsignedInteger('academic_period_id')->nullable();

            // Billing snapshot at time of generation
            $table->unsignedInteger('student_count');           // active students counted
            $table->decimal('rate_per_student', 8, 2);          // tier rate matched
            $table->unsignedInteger('tier_id')->nullable();     // which tier was matched
            $table->decimal('amount', 10, 2);                   // student_count × rate

            // Payment tracking
            // status: pending → paid → overdue (if past due_date and unpaid)
            $table->string('status')->default('pending');      // pending|paid|overdue|cancelled
            $table->string('invoice_number')->unique();
            $table->string('paystack_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            // When the invoice is due (school must pay by this date)
            $table->date('due_date')->nullable();

            // The term this invoice covers (label, e.g. "Term 1, 2025/2026")
            $table->string('term_label')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('per_student_invoices');
    }
};
