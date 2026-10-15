<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old check constraint and recreate with paystack included
        try {
            DB::statement('ALTER TABLE "owner_payments" DROP CONSTRAINT IF EXISTS "owner_payments_payment_method_check"');
        } catch (\Throwable $e) {}

        try {
            // Add new constraint with paystack included
            DB::statement("
                ALTER TABLE \"owner_payments\"
                ADD CONSTRAINT \"owner_payments_payment_method_check\"
                CHECK (payment_method IN ('cash','mobile_money','bank_transfer','card','paystack','other'))
            ");
        } catch (\Throwable $e) {}

        // Also change the column to remove any enum restriction — use plain varchar
        try {
            DB::statement('ALTER TABLE "owner_payments" ALTER COLUMN "payment_method" TYPE VARCHAR(255)');
        } catch (\Throwable $e) {}
    }

    public function down(): void {}
};
