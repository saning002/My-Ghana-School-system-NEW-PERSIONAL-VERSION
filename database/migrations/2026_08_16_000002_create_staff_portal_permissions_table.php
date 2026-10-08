<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Staff portal users (accountants, headmasters, etc.)
        Schema::create('staff_portal_users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['accountant', 'headmaster', 'headteacher', 'deputy']);
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('church_branch_id')->nullable()->constrained('church_branches')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        // Per-staff portal permissions (which admin sections they can access)
        Schema::create('staff_portal_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_portal_user_id')->constrained('staff_portal_users')->cascadeOnDelete();
            $table->string('permission'); // e.g. students, fees, reports, attendance, exams, timetable, calendar
            $table->timestamps();
            $table->unique(['staff_portal_user_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_portal_permissions');
        Schema::dropIfExists('staff_portal_users');
    }
};
