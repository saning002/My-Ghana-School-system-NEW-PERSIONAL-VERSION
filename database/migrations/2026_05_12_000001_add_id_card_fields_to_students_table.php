<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'photo')) {
                $table->string('photo')->nullable()->after('church_branch_id');
            }
            if (!Schema::hasColumn('students', 'signal')) {
                $table->string('signal')->nullable();
            }
            if (!Schema::hasColumn('students', 'position')) {
                $table->string('position')->nullable();
            }
            if (!Schema::hasColumn('students', 'date_issued')) {
                $table->date('date_issued')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $cols = array_filter(
                ['photo', 'signal', 'position', 'date_issued'],
                fn($c) => Schema::hasColumn('students', $c)
            );
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
