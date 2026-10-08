<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    /**
     * Show admin settings page
     */
    public function show()
    {
        $admin = auth()->user();
        
        // Branch admins can only edit their own settings
        if ($admin->isBranchAdmin() && !$admin->isSuperAdmin()) {
            // OK - branch admin editing own settings
        } elseif ($admin->isSuperAdmin()) {
            // OK - super admin can edit settings
        } else {
            abort(403, 'Unauthorized');
        }

        // Expose grading settings values to the view
        $gradingPreset = \App\Models\Setting::get('grading_preset', 'ghana_standard');
        $gradingScaleJson = \App\Models\Setting::get('grading_scale_json', json_encode([
            ['grade' => 'A1', 'min' => 80, 'point' => 4.0, 'description' => 'A1 DISTINCTION'],
            ['grade' => 'A2', 'min' => 70, 'point' => 3.7, 'description' => 'A2 UPPER DIVISION'],
            ['grade' => 'A3', 'min' => 60, 'point' => 3.3, 'description' => 'A3 LOWER DIVISION'],
            ['grade' => 'B1', 'min' => 50, 'point' => 3.0, 'description' => 'B1 CREDIT'],
            ['grade' => 'B2', 'min' => 40, 'point' => 2.0, 'description' => 'B2 PASS'],
            ['grade' => 'F',  'min' => 0,  'point' => 0.0, 'description' => 'F FAIL'],
        ], JSON_PRETTY_PRINT));

        $schoolName = Setting::get('school_name', 'School');
        $schoolSubtitle = Setting::get('school_subtitle', 'University College');
        $siteLogoUrl = Setting::get('site_logo_url', '');

        // Lecturer permission flags (all default true = enabled)
        $lecturerPermissions = [
            'attendance'   => (bool) Setting::get('lecturer_can_attendance',   true),
            'exams'        => (bool) Setting::get('lecturer_can_exams',        true),
            'reports'      => (bool) Setting::get('lecturer_can_reports',      true),
            'notifications'=> (bool) Setting::get('lecturer_can_notifications',true),
            'edit_profile' => (bool) Setting::get('lecturer_can_edit_profile', true),
        ];

        $sidebarLabels = [
            'programs'   => Setting::get('sidebar_programs_label', 'Programs'),
            'courses'    => Setting::get('sidebar_courses_label', 'Courses'),
            'students'   => Setting::get('sidebar_students_label', 'Students'),
            'lecturers'  => Setting::get(
                'sidebar_lecturers_label',
                Setting::get('sidebar_teachers_label', 'Teachers')
            ),
            'teachers'   => Setting::get(
                'sidebar_lecturers_label',
                Setting::get('sidebar_teachers_label', 'Teachers')
            ),
            'attendance' => Setting::get('sidebar_attendance_label', 'Attendance'),
            'exams'      => Setting::get('sidebar_exams_label', 'Exams & Results'),
            'fees'       => Setting::get('sidebar_fees_label', 'Fees & Payments'),
            'reports'    => Setting::get('sidebar_reports_label', 'Reports'),
            'portal'     => Setting::get(
                'sidebar_portal_label',
                Setting::get('sidebar_portals_label', 'Student Portal')
            ),
            'portals'    => Setting::get(
                'sidebar_portal_label',
                Setting::get('sidebar_portals_label', 'Student Portal')
            ),
            'settings'   => Setting::get('sidebar_settings_label', 'Settings'),
            'guide'      => Setting::get('sidebar_guide_label', 'Admin Guide'),
        ];

        return view('admin.settings.profile', compact(
            'admin',
            'gradingPreset',
            'gradingScaleJson',
            'schoolName',
            'schoolSubtitle',
            'siteLogoUrl',
            'sidebarLabels',
            'lecturerPermissions'
        ));
    }

    /**
     * Update branding and sidebar labels
     */
    public function update(Request $request)
    {
        $request->validate([
            'school_name'                  => 'required|string|max:120',
            'school_subtitle'              => 'nullable|string|max:120',
            'school_id_prefix'             => 'nullable|string|max:6',
            'site_logo'                    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sidebar_students_label'       => 'nullable|string|max:40',
            'sidebar_lecturers_label'      => 'nullable|string|max:40',
            'sidebar_programs_label'       => 'nullable|string|max:40',
            'sidebar_courses_label'        => 'nullable|string|max:40',
            'sidebar_attendance_label'     => 'nullable|string|max:40',
            'sidebar_exams_label'          => 'nullable|string|max:40',
            'sidebar_fees_label'           => 'nullable|string|max:40',
            'sidebar_reports_label'        => 'nullable|string|max:40',
            'sidebar_portal_label'         => 'nullable|string|max:40',
            'sidebar_settings_label'       => 'nullable|string|max:40',
            'sidebar_guide_label'          => 'nullable|string|max:40',
            'remove_site_logo'             => 'nullable|boolean',
            'grading_preset'               => 'nullable|in:ghana_standard,custom',
            'grading_scale_json'           => 'nullable|string',
            // Lecturer permissions (checkboxes — absent means false)
            'lecturer_can_attendance'      => 'nullable|boolean',
            'lecturer_can_exams'           => 'nullable|boolean',
            'lecturer_can_reports'         => 'nullable|boolean',
            'lecturer_can_notifications'   => 'nullable|boolean',
            'lecturer_can_edit_profile'    => 'nullable|boolean',
        ]);

        Setting::set('school_name', $request->school_name);
        Setting::set('school_subtitle', $request->school_subtitle ?? '');

        // School ID prefix (permanent student ID prefix)
        if ($request->filled('school_id_prefix')) {
            Setting::set('school_id_prefix', strtoupper(preg_replace('/[^A-Za-z]/', '', $request->school_id_prefix)));
        }

        // Sync name + tagline to the website SiteSetting as well
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                $siteSetting = \App\Models\Website\SiteSetting::instance();
                $siteSetting->update([
                    'site_name' => $request->school_name,
                    'tagline'   => $request->school_subtitle ?? $siteSetting->tagline,
                ]);
            }
        } catch (\Throwable $e) {
            // SiteSetting sync is non-critical — don't block the save
        }
        Setting::set('sidebar_students_label', $request->sidebar_students_label ?: 'Students');
        Setting::set('sidebar_lecturers_label', $request->sidebar_lecturers_label ?: 'Lecturers');
        Setting::set('sidebar_teachers_label', $request->sidebar_lecturers_label ?: 'Teachers');
        Setting::set('sidebar_programs_label', $request->sidebar_programs_label ?: 'Programs');
        Setting::set('sidebar_courses_label', $request->sidebar_courses_label ?: 'Courses');
        Setting::set('sidebar_attendance_label', $request->sidebar_attendance_label ?: 'Attendance');
        Setting::set('sidebar_exams_label', $request->sidebar_exams_label ?: 'Exams & Results');
        Setting::set('sidebar_fees_label', $request->sidebar_fees_label ?: 'Fees & Payments');
        Setting::set('sidebar_reports_label', $request->sidebar_reports_label ?: 'Reports');
        Setting::set('sidebar_portal_label', $request->sidebar_portal_label ?: 'Student Portal');
        Setting::set('sidebar_portals_label', $request->sidebar_portal_label ?: 'Student Portal');
        Setting::set('sidebar_settings_label', $request->sidebar_settings_label ?: 'Settings');
        Setting::set('sidebar_guide_label', $request->sidebar_guide_label ?: 'Admin Guide');

        if ($request->filled('remove_site_logo') && Setting::get('site_logo_url')) {
            $this->deleteOldLogo(Setting::get('site_logo_url'));
            Setting::set('site_logo_url', '');
        }

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $path = \App\Http\Controllers\Admin\StudentController::storePhotoFile($file, 'settings/logos');
            if ($path) {
                if (Setting::get('site_logo_url')) {
                    $this->deleteOldLogo(Setting::get('site_logo_url'));
                }
                Setting::set('site_logo_url', $path);
            }
        }

        // Save grading settings
        $preset = $request->grading_preset ?: 'ghana_standard';
        \App\Models\Setting::set('grading_preset', $preset);
        if ($preset === 'custom' && $request->filled('grading_scale_json')) {
            // Ensure it's valid JSON before saving
            try {
                $decoded = json_decode($request->grading_scale_json, true);
                if (is_array($decoded)) {
                    \App\Models\Setting::set('grading_scale_json', json_encode($decoded));
                }
            } catch (\Throwable $e) {
                // Ignore invalid JSON - do not overwrite existing scale
            }
        }

        // Save lecturer permission flags
        // Checkboxes are absent from the request when unchecked, so we treat missing as false.
        $lecturerPermissionKeys = [
            'lecturer_can_attendance',
            'lecturer_can_exams',
            'lecturer_can_reports',
            'lecturer_can_notifications',
            'lecturer_can_edit_profile',
        ];
        foreach ($lecturerPermissionKeys as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0');
        }

        return back()->with('success', 'Branding and sidebar labels updated successfully.');
    }

    /**
     * Update ONLY grading scale settings — does not touch branding or permissions.
     */
    public function updateGrading(Request $request)
    {
        $request->validate([
            'grading_preset'     => 'nullable|in:ghana_standard,custom',
            'grading_scale_json' => 'nullable|string',
        ]);

        $preset = $request->grading_preset ?: 'ghana_standard';
        Setting::set('grading_preset', $preset);

        if ($preset === 'custom' && $request->filled('grading_scale_json')) {
            try {
                $decoded = json_decode($request->grading_scale_json, true);
                if (is_array($decoded)) {
                    Setting::set('grading_scale_json', json_encode($decoded));
                }
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Grading scale saved successfully.');
    }

    /**
     * Update ONLY lecturer permission flags — does not touch branding or grading.
     */
    public function updatePermissions(Request $request)
    {
        $keys = [
            'lecturer_can_attendance',
            'lecturer_can_exams',
            'lecturer_can_reports',
            'lecturer_can_notifications',
            'lecturer_can_edit_profile',
        ];
        foreach ($keys as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0');
        }
        return back()->with('success', 'Teacher permissions saved successfully.');
    }

    /**
     * Update ONLY academic expectations.
     */
    public function updateAcademicExpectations(Request $request)
    {
        $request->validate([
            'expected_classworks' => 'nullable|integer|min:0',
            'expected_homeworks'  => 'nullable|integer|min:0',
            'expected_tests'      => 'nullable|integer|min:0',
        ]);

        $keys = ['expected_classworks', 'expected_homeworks', 'expected_tests'];
        foreach ($keys as $key) {
            if ($request->filled($key)) {
                Setting::set($key, $request->$key);
            }
        }
        return back()->with('success', 'Academic expectations saved successfully.');
    }

    /**
     * Update ONLY SBA sub-score weights and labels.
     */
    public function updateSba(Request $request)
    {
        $request->validate([
            'sba_test1_weight'     => 'nullable|numeric|min:0',
            'sba_groupwork_weight' => 'nullable|numeric|min:0',
            'sba_test2_weight'     => 'nullable|numeric|min:0',
            'sba_project_weight'   => 'nullable|numeric|min:0',
            'sba_test1_label'      => 'nullable|string|max:40',
            'sba_groupwork_label'  => 'nullable|string|max:40',
            'sba_test2_label'      => 'nullable|string|max:40',
            'sba_project_label'    => 'nullable|string|max:40',
            'quiz_percentage'      => 'nullable|numeric|min:0|max:100',
            'exam_percentage'      => 'nullable|numeric|min:0|max:100',
        ]);

        $fields = [
            'sba_test1_weight','sba_groupwork_weight','sba_test2_weight','sba_project_weight',
            'sba_test1_label','sba_groupwork_label','sba_test2_label','sba_project_label',
            'quiz_percentage','exam_percentage',
        ];
        foreach ($fields as $f) {
            if ($request->filled($f)) {
                Setting::set($f, $request->$f);
            }
        }

        return back()->with('success', 'SBA weights and score settings saved successfully.');
    }

    private function deleteOldLogo(string $path): void
    {
        if (str_contains($path, 'cloudinary.com')) {
            \App\Services\CloudinaryService::delete($path);
        } elseif (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Update login background image
     */
    public function updateLoginBackground(Request $request)
    {
        $request->validate([
            'login_background' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max for image
        ]);

        $admin = auth()->user();

        // Both branch admins and super admins can customize their login background
        if (!($admin->isBranchAdmin() || $admin->isSuperAdmin())) {
            return back()->with('error', 'Only administrators can customize their login background.');
        }

        try {
            if ($request->hasFile('login_background')) {
                $file = $request->file('login_background');

                // Try Cloudinary first, then fall back to local storage
                $url = $this->uploadLoginBackground($file);

                if ($url) {
                    // Delete old background if it exists
                    if ($admin->login_background_url) {
                        $this->deleteOldBackground($admin->login_background_url);
                    }

                    // Update user record
                    $admin->update(['login_background_url' => $url]);

                    return back()->with('success', 'Login background updated successfully!');
                } else {
                    return back()->with('error', 'Failed to upload background image. Please try again.');
                }
            } else {
                // Clear background
                if ($admin->login_background_url) {
                    $this->deleteOldBackground($admin->login_background_url);
                    $admin->update(['login_background_url' => null]);
                    return back()->with('success', 'Login background removed successfully!');
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to update login background', [
                'admin_id' => $admin->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'An error occurred while updating the background.');
        }

        return back();
    }

    /**
     * Upload login background file
     */
    private function uploadLoginBackground($file): ?string
    {
        try {
            // Try Cloudinary first
            if (CloudinaryService::isConfigured()) {
                $url = CloudinaryService::upload($file, 'admin/login-backgrounds');
                if ($url) {
                    return $url;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Cloudinary upload failed for login background: ' . $e->getMessage());
        }

        // Fall back to local storage
        try {
            $directory = 'admin/login-backgrounds';
            $fullDir = Storage::disk('public')->path($directory);
            
            if (!File::isDirectory($fullDir)) {
                File::makeDirectory($fullDir, 0775, true, true);
            }

            $stored = Storage::disk('public')->putFile($directory, $file);
            if ($stored) {
                return $stored;
            }
        } catch (\Throwable $e) {
            \Log::warning('Local storage upload failed for login background: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Delete old background file
     */
    private function deleteOldBackground(string $url): void
    {
        try {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                // URL from Cloudinary
                CloudinaryService::delete($url);
            } elseif (Storage::disk('public')->exists($url)) {
                // Local storage
                Storage::disk('public')->delete($url);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to delete old login background: ' . $e->getMessage());
        }
    }
}
