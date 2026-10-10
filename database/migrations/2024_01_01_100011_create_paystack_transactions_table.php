<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Central tracking table for all Paystack transactions (both flows).
 * Stored in the OWNER (central) database.
 *
 * type:
 *   - subscription  → school paying owner for platform access
 *   - school_fee    → student/parent paying school fees
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paystack_transactions', function (Blueprint $table) {
            $table->id();

            // Which type of payment
            $table->enum('type', ['subscription', 'school_fee']);

            // For subscription payments — which tenant is paying
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();

            // For school fee payments — which tenant's school the student belongs to
            $table->unsignedBigInteger('student_id')->nullable(); // ID in tenant DB
            $table->string('student_name')->nullable();

            // Payer details
            $table->string('payer_email');
            $table->string('payer_name')->nullable();

            // Transaction details
            $table->string('reference')->unique();
            $table->decimal('amount', 10, 2);          // in GHS
            $table->string('currency', 10)->default('GHS');
            $table->integer('months_paid')->default(1); // for subscription type
            $table->string('description')->nullable();

            // Paystack response
            $table->enum('status', ['pending', 'success', 'failed', 'abandoned'])->default('pending');
            $table->string('paystack_id')->nullable();     // Paystack transaction ID
            $table->string('channel')->nullable();         // card, mobile_money, bank
            $table->text('paystack_response')->nullable(); // full JSON response

            // What was created after success
            $table->unsignedBigInteger('owner_payment_id')->nullable(); // for subscription
            $table->unsignedBigInteger('school_payment_id')->nullable(); // for school_fee

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paystack_transactions');
    }
};
