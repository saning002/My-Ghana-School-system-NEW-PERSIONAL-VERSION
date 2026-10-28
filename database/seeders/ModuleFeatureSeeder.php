<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

/**
 * Seeds the features table with all system modules.
 * Safe to run multiple times — uses updateOrCreate on 'key'.
 */
class ModuleFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            // ── Core ────────────────────────────────────────────────────────
            ['key' => 'students',         'label' => 'Students Management',     'description' => 'Add, edit, view and manage student records.',              'group' => 'Core',    'sort_order' => 10],
            ['key' => 'teachers',         'label' => 'Teachers / Lecturers',    'description' => 'Manage teaching staff, assignments and work logs.',         'group' => 'Core',    'sort_order' => 20],
            ['key' => 'programs',         'label' => 'Programs / Classes',      'description' => 'Create and manage school programs or class levels.',         'group' => 'Core',    'sort_order' => 30],
            ['key' => 'courses',          'label' => 'Courses / Subjects',      'description' => 'Create subjects and link them to programs and teachers.',    'group' => 'Core',    'sort_order' => 40],
            ['key' => 'dashboard',        'label' => 'Dashboard & Analytics',   'description' => 'Overview dashboard with charts and recent activity.',        'group' => 'Core',    'sort_order' => 5],

            // ── Academic ─────────────────────────────────────────────────────
            ['key' => 'attendance',       'label' => 'Attendance Tracking',     'description' => 'Mark and view student attendance by date or period.',       'group' => 'Academic','sort_order' => 10],
            ['key' => 'exams',            'label' => 'Exams & Scores',          'description' => 'Enter SBA and exam scores, generate report cards.',         'group' => 'Academic','sort_order' => 20],
            ['key' => 'academic_sessions','label' => 'Academic Sessions / Terms','description' => 'Define school years and term/semester periods.',            'group' => 'Academic','sort_order' => 30],
            ['key' => 'timetable',        'label' => 'Timetable',               'description' => 'Create and view weekly class timetables.',                  'group' => 'Academic','sort_order' => 40],
            ['key' => 'scheme_of_learning','label'=> 'Scheme of Learning',      'description' => 'Teachers log weekly topics and lesson plans.',               'group' => 'Academic','sort_order' => 50],
            ['key' => 'calendar',         'label' => 'School Calendar',         'description' => 'Manage school events and public holidays.',                  'group' => 'Academic','sort_order' => 60],
            ['key' => 'student_reports',  'label' => 'Student Reports (Head Teacher)', 'description' => 'Head teacher / class teacher comments and conduct ratings.', 'group' => 'Academic','sort_order' => 70],

            // ── Fees & Finance ────────────────────────────────────────────────
            ['key' => 'fees',             'label' => 'Fees & Payments',         'description' => 'Record and track term fees and student payments.',          'group' => 'Finance', 'sort_order' => 10],
            ['key' => 'fees_paystack',    'label' => 'Online Payments (Paystack)', 'description' => 'Students/parents pay fees online via Paystack.',         'group' => 'Finance', 'sort_order' => 20],
            ['key' => 'daily_fees',       'label' => 'Daily Attendance Fees',   'description' => 'Charge per-day fees linked to student attendance.',         'group' => 'Finance', 'sort_order' => 30],
            ['key' => 'fee_reminders',    'label' => 'Fee Reminders (SMS/Email)', 'description' => 'Send automated fee reminder messages to parents.',        'group' => 'Finance', 'sort_order' => 40],

            // ── Communication ─────────────────────────────────────────────────
            ['key' => 'notifications',    'label' => 'Notifications & Messaging', 'description' => 'Send school-wide or targeted notifications to students.', 'group' => 'Communication', 'sort_order' => 10],
            ['key' => 'portals',          'label' => 'Student / Parent Portal', 'description' => 'Student portal for viewing results, fees and notifications.','group' => 'Communication', 'sort_order' => 20],
            ['key' => 'staff_portal',     'label' => 'Staff Portals (Accountant / HM)', 'description' => 'Limited-access portals for accountants and headmasters.', 'group' => 'Communication', 'sort_order' => 30],

            // ── Reports ───────────────────────────────────────────────────────
            ['key' => 'reports',          'label' => 'Reports & Analytics',     'description' => 'Generate attendance, financial and academic reports.',      'group' => 'Reports', 'sort_order' => 10],

            // ── Website ───────────────────────────────────────────────────────
            ['key' => 'website',          'label' => 'Public Website (CMS)',    'description' => 'School website with hero, programs, contact, gallery etc.', 'group' => 'Website', 'sort_order' => 10],
        ];

        foreach ($modules as $module) {
            Feature::updateOrCreate(
                ['key' => $module['key']],
                array_merge($module, ['is_active' => true])
            );
        }

        $this->command->info('✅ ' . count($modules) . ' module features seeded/updated.');
    }
}
