<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            Schema::table('website_programs', function (Blueprint $table) {
                $table->string('level', 100)->nullable()->change();
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try {
            Schema::table('website_programs', function (Blueprint $table) {
                $table->string('level', 30)->nullable()->change();
            });
        } catch (\Throwable $e) {}
    }
};
