<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Paystack tracking columns to the school's payments table.
 * This migration runs against each tenant (school) database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Only add if they don't already exist
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->default('cash')->after('notes');
                // values: cash | mobile_money | bank_transfer | card | paystack | other
            }
            if (!Schema::hasColumn('payments', 'paystack_reference')) {
                $table->string('paystack_reference')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'paystack_status')) {
                // pending | success | failed
                $table->string('paystack_status')->nullable()->after('paystack_reference');
            }
            if (!Schema::hasColumn('payments', 'paystack_channel')) {
                // card | mobile_money | bank etc
                $table->string('paystack_channel')->nullable()->after('paystack_status');
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('paystack_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumnIfExists('payment_method');
            $table->dropColumnIfExists('paystack_reference');
            $table->dropColumnIfExists('paystack_status');
            $table->dropColumnIfExists('paystack_channel');
            $table->dropColumnIfExists('transaction_id');
        });
    }
};
