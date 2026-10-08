<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('student_notifications', 'recipient_type')) {
                $table->string('recipient_type', 20)->default('student')->after('audience');
            }
            if (!Schema::hasColumn('student_notifications', 'lecturer_id')) {
                $table->foreignId('lecturer_id')
                      ->nullable()
                      ->after('student_id')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_notifications', function (Blueprint $table) {
            $table->dropForeign(['lecturer_id']);
            $table->dropColumn(['recipient_type', 'lecturer_id']);
        });
    }
};
