<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * term_fee_bills — one row per student per academic session/term.
 * Tracks exactly what was billed for that term.
 * Run against TENANT DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_fee_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('academic_session_id');
            $table->decimal('program_fee', 10, 2)->default(0);
            $table->decimal('exam_fee', 10, 2)->default(0);
            $table->decimal('other_fees', 10, 2)->default(0);
            $table->string('other_fees_label')->nullable();   // e.g. "Activity Fee"
            $table->decimal('total_billed', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_session_id'], 'unique_student_term_bill');
        });

        // Add missing columns to payments table for term tracking
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (!Schema::hasColumn('payments', 'receipt_number')) {
                    $table->string('receipt_number')->nullable()->after('id');
                }
                if (!Schema::hasColumn('payments', 'academic_session_id')) {
                    $table->unsignedBigInteger('academic_session_id')->nullable()->after('payment_date');
                }
                if (!Schema::hasColumn('payments', 'payer_name')) {
                    $table->string('payer_name')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('payments', 'payer_phone')) {
                    $table->string('payer_phone')->nullable()->after('payer_name');
                }
                if (!Schema::hasColumn('payments', 'receipt_path')) {
                    $table->string('receipt_path')->nullable()->after('payer_phone');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('term_fee_bills');
    }
};
