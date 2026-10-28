<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Cached JSON of enabled module keys — avoids join on every request
            // e.g. ["students","fees","exams","website"]
            $table->json('modules_json')->nullable()->after('notes');

            // Admin panel theme set from owner panel
            // e.g. {"adminPrimary":"#e94560","adminNav":"#0f3460","primary":"#f59e0b",...}
            $table->json('theme_json')->nullable()->after('modules_json');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['modules_json', 'theme_json']);
        });
    }
};
