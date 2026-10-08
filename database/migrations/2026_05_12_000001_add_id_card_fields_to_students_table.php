<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('church_branch_id');
            $table->string('signal')->nullable()->after('photo');
            $table->string('position')->nullable()->after('signal');
            $table->date('date_issued')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['photo', 'signal', 'position', 'date_issued']);
        });
    }
};
