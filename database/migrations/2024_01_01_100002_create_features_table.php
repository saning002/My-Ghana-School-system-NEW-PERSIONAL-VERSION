<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();        // e.g. attendance, exams, fees, reports
            $table->string('label');                // Human-readable: "Attendance Management"
            $table->text('description')->nullable();
            $table->string('group')->nullable();    // e.g. academics, finance, communication
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed default features
        $features = [
            ['key' => 'attendance',          'label' => 'Attendance Management',       'group' => 'academics'],
            ['key' => 'exams',               'label' => 'Exam Management',             'group' => 'academics'],
            ['key' => 'exam_questions',      'label' => 'Exam Questions Bank',         'group' => 'academics'],
            ['key' => 'results',             'label' => 'Results & Grading',           'group' => 'academics'],
            ['key' => 'report_cards',        'label' => 'Report Cards',                'group' => 'academics'],
            ['key' => 'scheme_of_learning',  'label' => 'Scheme of Learning',          'group' => 'academics'],
            ['key' => 'timetable',           'label' => 'Timetable',                   'group' => 'academics'],
            ['key' => 'fees',                'label' => 'Fees Management',             'group' => 'finance'],
            ['key' => 'daily_fees',          'label' => 'Daily Fees',                  'group' => 'finance'],
            ['key' => 'financial_reports',   'label' => 'Financial Reports',           'group' => 'finance'],
            ['key' => 'students',            'label' => 'Student Management',          'group' => 'core'],
            ['key' => 'lecturers',           'label' => 'Lecturer Management',         'group' => 'core'],
            ['key' => 'programs',            'label' => 'Programs Management',         'group' => 'core'],
            ['key' => 'courses',             'label' => 'Courses Management',          'group' => 'core'],
            ['key' => 'notifications',       'label' => 'Notifications',               'group' => 'communication'],
            ['key' => 'school_events',       'label' => 'School Events',               'group' => 'communication'],
            ['key' => 'promotions',          'label' => 'Student Promotions',          'group' => 'academics'],
            ['key' => 'staff_portal',        'label' => 'Staff Portal',                'group' => 'portal'],
            ['key' => 'student_portal',      'label' => 'Student Portal',              'group' => 'portal'],
            ['key' => 'work_logs',           'label' => 'Teacher Work Logs',           'group' => 'academics'],
            ['key' => 'website',             'label' => 'School Website',              'group' => 'website'],
            ['key' => 'document_verify',     'label' => 'Document Verification',       'group' => 'portal'],
            ['key' => 'class_register',      'label' => 'Class Register',              'group' => 'academics'],
            ['key' => 'order_of_merit',      'label' => 'Order of Merit',              'group' => 'academics'],
        ];

        foreach ($features as $i => $feature) {
            \DB::table('features')->insert(array_merge($feature, [
                'is_active'  => true,
                'sort_order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
