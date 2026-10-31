<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'messaging_credits' to the paystack_transactions type enum/check constraint.
 * The original migration only allowed 'subscription' and 'school_fee'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop the old check constraint and add a new one
        DB::statement("ALTER TABLE paystack_transactions DROP CONSTRAINT IF EXISTS paystack_transactions_type_check");
        DB::statement("ALTER TABLE paystack_transactions ADD CONSTRAINT paystack_transactions_type_check CHECK (type IN ('subscription', 'school_fee', 'messaging_credits'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE paystack_transactions DROP CONSTRAINT IF EXISTS paystack_transactions_type_check");
        DB::statement("ALTER TABLE paystack_transactions ADD CONSTRAINT paystack_transactions_type_check CHECK (type IN ('subscription', 'school_fee'))");
    }
};
