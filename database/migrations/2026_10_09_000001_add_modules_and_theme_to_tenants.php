<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'modules_json')) {
                $table->json('modules_json')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'theme_json')) {
                $table->json('theme_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            foreach (['modules_json', 'theme_json'] as $col) {
                if (Schema::hasColumn('tenants', $col)) $table->dropColumn($col);
            }
        });
    }
};
