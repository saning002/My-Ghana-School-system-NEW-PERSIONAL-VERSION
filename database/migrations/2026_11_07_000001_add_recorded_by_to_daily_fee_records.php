<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_fee_records', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_fee_records', 'recorded_by')) {
                $table->unsignedBigInteger('recorded_by')->nullable();
            }
            if (!Schema::hasColumn('daily_fee_records', 'notes')) {
                $table->string('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_fee_records', function (Blueprint $table) {
            if (Schema::hasColumn('daily_fee_records', 'recorded_by')) {
                $table->dropColumn('recorded_by');
            }
            if (Schema::hasColumn('daily_fee_records', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
