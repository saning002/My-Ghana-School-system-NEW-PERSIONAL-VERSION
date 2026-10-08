<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates two tables:
 *
 * 1. attribute_definitions  — admin-defined list of personality/conduct attributes
 *    (e.g. "Neatness", "Honesty", "Initiative") with a type (personality|conduct|attitude|interest)
 *
 * 2. student_reports  — per-student per-program per-attempt report record
 *    stores conduct text, attitude text, interest text, class teacher remark,
 *    head teacher remark, promoted_to, and a JSON ratings field
 *    that holds { attribute_definition_id: "Very Good"|"Good"|"Average"|"Weak / Poor" }
 *
 * 3. student_report_attributes — individual rating rows (one per attribute per student report)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Attribute definitions
        Schema::create('attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['personality', 'conduct', 'attitude', 'interest'])->default('personality');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Student reports (one per student + program + attempt)
        Schema::create('student_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->string('conduct')->nullable();
            $table->string('attitude')->nullable();
            $table->string('interest')->nullable();
            $table->text('class_teacher_remark')->nullable();
            $table->text('head_teacher_remark')->nullable();
            $table->string('promoted_to')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'program_id', 'attempt']);
        });

        // Individual attribute ratings
        Schema::create('student_report_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_report_id')->constrained('student_reports')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')->constrained('attribute_definitions')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt')->default(1);
            $table->enum('rating', ['Very Good', 'Good', 'Average', 'Weak / Poor'])->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'program_id', 'attempt', 'attribute_definition_id'], 'uniq_student_attr');
        });

        // Seed the 14 default personality attributes from the physical form
        $defaults = [
            ['name' => 'Neatness',                     'type' => 'personality', 'sort_order' => 1],
            ['name' => 'Courtesy',                     'type' => 'personality', 'sort_order' => 2],
            ['name' => 'Tolerance / Temperament',      'type' => 'personality', 'sort_order' => 3],
            ['name' => 'Sociable',                     'type' => 'personality', 'sort_order' => 4],
            ['name' => 'Initiative',                   'type' => 'personality', 'sort_order' => 5],
            ['name' => 'Imaginative & Creative / Ability','type' => 'personality', 'sort_order' => 6],
            ['name' => 'Dependability',                'type' => 'personality', 'sort_order' => 7],
            ['name' => 'Honesty',                      'type' => 'personality', 'sort_order' => 8],
            ['name' => 'Interest in Practical work',   'type' => 'personality', 'sort_order' => 9],
            ['name' => 'Leadership Quality',           'type' => 'personality', 'sort_order' => 10],
            ['name' => 'Understanding of lessons',     'type' => 'personality', 'sort_order' => 11],
            ['name' => 'Interest in Studies',          'type' => 'personality', 'sort_order' => 12],
            ['name' => 'Observation of objects',       'type' => 'personality', 'sort_order' => 13],
            ['name' => 'Co-operations with mates',     'type' => 'personality', 'sort_order' => 14],
        ];
        $now = now()->toDateTimeString();
        foreach ($defaults as $d) {
            \DB::table('attribute_definitions')->insert(array_merge($d, [
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_report_attributes');
        Schema::dropIfExists('student_reports');
        Schema::dropIfExists('attribute_definitions');
    }
};
