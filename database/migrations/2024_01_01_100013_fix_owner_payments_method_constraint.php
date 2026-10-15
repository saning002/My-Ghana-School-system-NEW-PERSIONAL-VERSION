<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old check constraint that doesn't include 'paystack'
        // and recreate it with all allowed payment methods
        try {
            DB::statement('ALTER TABLE "owner_payments" DROP CONSTRAINT IF EXISTS "owner_payments_payment_method_check"');
        } catch (\Throwable $e) {}

        try {
            DB::statement("
                ALTER TABLE \"owner_payments\"
                ADD CONSTRAINT \"owner_payments_payment_method_check\"
                CHECK (payment_method IN ('cash','mobile_money','bank_transfer','card','paystack','other'))
            ");
        } catch (\Throwable $e) {}
    }

    public function down(): void {}
};
