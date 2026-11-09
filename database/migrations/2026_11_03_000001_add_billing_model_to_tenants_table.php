<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // 'flat'        = fixed annual/monthly subscription (existing behaviour)
            // 'per_student' = charged per active student per term
            $table->string('billing_model')->default('flat')->after('plan_id');

            // GHS amount charged per student per term (used when billing_model = 'per_student')
            $table->decimal('per_student_rate', 8, 2)->default(0)->after('billing_model');

            // How many terms per academic year (2 or 3) — for invoice generation
            $table->unsignedTinyInteger('billing_terms_per_year')->default(3)->after('per_student_rate');

            // Flags for optional add-ons bundled into the per-student rate
            // e.g. SMS attendance alerts included in subscription instead of credit top-ups
            $table->boolean('billing_includes_sms')->default(false)->after('billing_terms_per_year');

            // Calculated last invoice amount (cached, updated at invoice generation)
            $table->decimal('last_invoice_amount', 10, 2)->nullable()->after('billing_includes_sms');
            $table->unsignedInteger('last_invoice_student_count')->nullable()->after('last_invoice_amount');
            $table->timestamp('last_invoiced_at')->nullable()->after('last_invoice_student_count');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'billing_model',
                'per_student_rate',
                'billing_terms_per_year',
                'billing_includes_sms',
                'last_invoice_amount',
                'last_invoice_student_count',
                'last_invoiced_at',
            ]);
        });
    }
};
