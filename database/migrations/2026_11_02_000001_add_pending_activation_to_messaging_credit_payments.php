<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messaging_credit_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('messaging_credit_payments', 'credits_pending')) {
                $table->boolean('credits_pending')->default(false);
            }
            if (!Schema::hasColumn('messaging_credit_payments', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }
            if (!Schema::hasColumn('messaging_credit_payments', 'activated_by')) {
                $table->unsignedBigInteger('activated_by')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('messaging_credit_payments', function (Blueprint $table) {
            foreach (['credits_pending', 'activated_at', 'activated_by'] as $col) {
                if (Schema::hasColumn('messaging_credit_payments', $col)) $table->dropColumn($col);
            }
        });
    }
};
