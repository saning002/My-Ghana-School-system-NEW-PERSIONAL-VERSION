<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_definitions', function (Blueprint $table) {
            if (!Schema::hasColumn('attribute_definitions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
            if (!Schema::hasColumn('attribute_definitions', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attribute_definitions', function (Blueprint $table) {
            if (Schema::hasColumn('attribute_definitions', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
