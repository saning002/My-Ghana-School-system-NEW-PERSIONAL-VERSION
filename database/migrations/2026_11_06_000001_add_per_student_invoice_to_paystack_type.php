<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds 'per_student_invoice' to the paystack_transactions type check constraint.
 * The previous constraint only allowed 'subscription', 'school_fee', 'messaging_credits'.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE paystack_transactions DROP CONSTRAINT IF EXISTS paystack_transactions_type_check");
        DB::statement("ALTER TABLE paystack_transactions ADD CONSTRAINT paystack_transactions_type_check CHECK (type IN ('subscription', 'school_fee', 'messaging_credits', 'per_student_invoice'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE paystack_transactions DROP CONSTRAINT IF EXISTS paystack_transactions_type_check");
        DB::statement("ALTER TABLE paystack_transactions ADD CONSTRAINT paystack_transactions_type_check CHECK (type IN ('subscription', 'school_fee', 'messaging_credits'))");
    }
};
