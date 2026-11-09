<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_branches', function (Blueprint $table) {
            if (!Schema::hasColumn('church_branches', 'code')) {
                $table->string('code', 10)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('church_branches', function (Blueprint $table) {
            if (Schema::hasColumn('church_branches', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
