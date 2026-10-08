<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds default system settings.
 * Uses updateOrCreate keyed on 'key' — NEVER overwrites values
 * that the admin has already saved, even after a redeploy.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── School identity ───────────────────────────────────────────
            'school_name'     => 'Focus On Christ',
            'school_subtitle' => 'Ministerial University College',

            // ── Sidebar labels ────────────────────────────────────────────
            'sidebar_students_label'   => 'Students',
            'sidebar_lecturers_label'  => 'Teachers',
            'sidebar_teachers_label'   => 'Teachers',
            'sidebar_programs_label'   => 'Programs',
            'sidebar_courses_label'    => 'Courses',
            'sidebar_attendance_label' => 'Attendance',
            'sidebar_exams_label'      => 'Exams & Results',
            'sidebar_fees_label'       => 'Fees & Payments',
            'sidebar_reports_label'    => 'Reports',
            'sidebar_portal_label'     => 'Student Portal',
            'sidebar_portals_label'    => 'Student Portal',
            'sidebar_settings_label'   => 'Settings',
            'sidebar_guide_label'      => 'Admin Guide',

            // ── Score weighting ───────────────────────────────────────────
            'quiz_percentage' => '50',
            'exam_percentage' => '50',

            // ── SBA sub-score components ──────────────────────────────────
            'sba_test1_weight'     => '25',
            'sba_groupwork_weight' => '25',
            'sba_test2_weight'     => '25',
            'sba_project_weight'   => '25',
            'sba_test1_label'      => 'Test 1',
            'sba_groupwork_label'  => 'Group Work',
            'sba_test2_label'      => 'Test 2',
            'sba_project_label'    => 'Project Work',

            // ── Grading ───────────────────────────────────────────────────
            'grading_preset' => 'ghana_standard',

            // ── Lecturer permissions ──────────────────────────────────────
            'lecturer_can_attendance'    => '1',
            'lecturer_can_exams'         => '1',
            'lecturer_can_reports'       => '1',
            'lecturer_can_notifications' => '1',
            'lecturer_can_edit_profile'  => '1',

            // ── Portal access ─────────────────────────────────────────────
            'portal_access'          => '0',
            'teachers_portal_access' => '1',

            // ── Fee mode ──────────────────────────────────────────────────
            'fee_mode'           => 'program_fees', // or 'daily_fees'
            'daily_fee_rate'     => '5.00',         // GH₵ per school day

            // ── Student ID prefix ──────────────────────────────────────────
            'school_id_prefix'   => 'FOC',          // 2-4 letters, e.g. FOC/2026/0001
        ];

        foreach ($defaults as $key => $value) {
            // Only insert if the key does not already exist in the DB.
            // This means admin changes survive redeployments.
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::create(['key' => $key, 'value' => $value]);
            }
        }
    }
}

// NOTE: extra fee_mode defaults added here — appended safely
// The run() method above handles all keys via doesntExist() guards.
// fee_mode and daily_fee_rate are added to the $defaults array inside run().
