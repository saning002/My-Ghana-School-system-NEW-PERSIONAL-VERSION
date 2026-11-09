<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('program_fees', 'church_branch_id')) {
                $table->foreignId('church_branch_id')->nullable()->after('program_id')->constrained('church_branches')->nullOnDelete();
            }
            try { $table->unique(['program_id', 'church_branch_id']); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('program_fees', function (Blueprint $table) {
            try { $table->dropUnique(['program_id', 'church_branch_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('program_fees', 'church_branch_id')) {
                $table->dropForeign(['church_branch_id']);
                $table->dropColumn('church_branch_id');
            }
        });
    }
};
