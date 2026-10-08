<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_branches', function (Blueprint $table) {
            // Short uppercase code used in student IDs, e.g. KA, HO, AC
            $table->string('code', 10)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('church_branches', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
