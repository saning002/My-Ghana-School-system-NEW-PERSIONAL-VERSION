<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messaging_credit_payments', function (Blueprint $table) {
            // 'pending_activation' = paid but Arkesel balance was insufficient at time of purchase
            // 'completed'          = credits added to school immediately
            // The 'status' column already exists — we just add the new possible value via a default
            // and a helper boolean column for fast querying
            $table->boolean('credits_pending')->default(false)->after('status');
            $table->timestamp('activated_at')->nullable()->after('credits_pending');
            $table->unsignedBigInteger('activated_by')->nullable()->after('activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('messaging_credit_payments', function (Blueprint $table) {
            $table->dropColumn(['credits_pending', 'activated_at', 'activated_by']);
        });
    }
};
