<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Clear any old placeholder exam scores in the database to prevent duplicate entries
        DB::table('exam_scores')->delete();

        // NOTE: Run ImportCoursesAndResultsSeeder separately once it is created:
        // php artisan db:seed --class=ImportCoursesAndResultsSeeder
    }

    public function down()
    {
        DB::table('exam_scores')->delete();
    }
};
