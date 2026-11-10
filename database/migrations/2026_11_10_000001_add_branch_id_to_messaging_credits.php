<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add branch_id to messaging_credits so each branch can have
 * its own dedicated credit pool separate from the school-wide shared pool.
 * branch_id = NULL means the school-wide shared pool.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messaging_credits', function (Blueprint $table) {
            // Drop the old unique constraint on tenant_id alone
            try {
                $table->dropUnique(['tenant_id']);
            } catch (\Throwable $e) {}

            // Add branch_id — nullable (NULL = school-wide shared pool)
            if (!Schema::hasColumn('messaging_credits', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('tenant_id');
            }

            // Add is_shared flag for clarity
            if (!Schema::hasColumn('messaging_credits', 'is_shared')) {
                $table->boolean('is_shared')->default(true)->after('branch_id');
            }

            // New unique: one record per tenant+branch combination
            try {
                $table->unique(['tenant_id', 'branch_id']);
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('messaging_credits', function (Blueprint $table) {
            try { $table->dropUnique(['tenant_id', 'branch_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('messaging_credits', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('messaging_credits', 'is_shared')) {
                $table->dropColumn('is_shared');
            }
            try { $table->unique('tenant_id'); } catch (\Throwable $e) {}
        });
    }
};
