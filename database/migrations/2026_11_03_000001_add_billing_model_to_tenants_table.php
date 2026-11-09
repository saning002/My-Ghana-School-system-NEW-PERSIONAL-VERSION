<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'billing_model')) {
                $table->string('billing_model')->default('flat');
            }
            if (!Schema::hasColumn('tenants', 'per_student_rate')) {
                $table->decimal('per_student_rate', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('tenants', 'billing_terms_per_year')) {
                $table->unsignedTinyInteger('billing_terms_per_year')->default(3);
            }
            if (!Schema::hasColumn('tenants', 'billing_includes_sms')) {
                $table->boolean('billing_includes_sms')->default(false);
            }
            if (!Schema::hasColumn('tenants', 'last_invoice_amount')) {
                $table->decimal('last_invoice_amount', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('tenants', 'last_invoice_student_count')) {
                $table->unsignedInteger('last_invoice_student_count')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'last_invoiced_at')) {
                $table->timestamp('last_invoiced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $cols = ['billing_model', 'per_student_rate', 'billing_terms_per_year', 'billing_includes_sms', 'last_invoice_amount', 'last_invoice_student_count', 'last_invoiced_at'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('tenants', $c));
            if (!empty($existing)) $table->dropColumn($existing);
        });
    }
};
