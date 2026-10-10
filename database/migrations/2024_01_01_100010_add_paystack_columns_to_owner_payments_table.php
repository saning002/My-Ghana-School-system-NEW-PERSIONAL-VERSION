<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Paystack tracking columns to the owner's subscription payments table.
 * This migration runs against the central (owner) database.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('owner_payments', 'paystack_reference')) {
                $table->string('paystack_reference')->nullable()->after('receipt_path');
            }
            if (!Schema::hasColumn('owner_payments', 'paystack_status')) {
                // pending | success | failed
                $table->string('paystack_status')->nullable()->after('paystack_reference');
            }
            if (!Schema::hasColumn('owner_payments', 'paystack_channel')) {
                $table->string('paystack_channel')->nullable()->after('paystack_status');
            }
            if (!Schema::hasColumn('owner_payments', 'paid_online')) {
                $table->boolean('paid_online')->default(false)->after('paystack_channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('owner_payments', function (Blueprint $table) {
            $table->dropColumnIfExists('paystack_reference');
            $table->dropColumnIfExists('paystack_status');
            $table->dropColumnIfExists('paystack_channel');
            $table->dropColumnIfExists('paid_online');
        });
    }
};
