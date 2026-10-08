<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PortalSettingsController extends Controller
{
    public function index()
    {
        $portalOpen            = Setting::portalIsOpen();
        $portalMessage         = Setting::get('portal_message', '');
        $teachersPortalOpen    = Setting::teachersPortalIsOpen();
        $teachersPortalMessage = Setting::get('teachers_portal_message', '');

        // Teacher feature permissions — all default to ON (true)
        // Admin can turn individual ones OFF from this page
        $teacherPermissions = [
            'attendance'    => 'Mark & View Attendance',
            'exams'         => 'Enter & View Exam Scores',
            'reports'       => 'View Reports & Report Cards',
            'notifications' => 'Receive Notifications',
            'edit_profile'  => 'Edit Own Profile',
        ];

        $teacherPermValues = [];
        foreach ($teacherPermissions as $key => $label) {
            // Default is true — shown as ON unless admin explicitly turned it OFF
            $teacherPermValues[$key] = Setting::lecturerCan($key);
        }

        return view('admin.portal-settings', compact(
            'portalOpen', 'portalMessage',
            'teachersPortalOpen', 'teachersPortalMessage',
            'teacherPermissions', 'teacherPermValues'
        ));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'portal_access'           => 'nullable|in:0,1',
            'portal_message'          => 'nullable|string|max:500',
            'teachers_portal_access'  => 'nullable|in:0,1',
            'teachers_portal_message' => 'nullable|string|max:500',
            'report_card_orientation' => 'nullable|in:portrait,landscape',
        ]);

        if ($request->has('portal_access')) {
            Setting::set('portal_access', $request->portal_access);
        }
        Setting::set('portal_message', $request->portal_message ?? '');

        if ($request->has('teachers_portal_access')) {
            Setting::set('teachers_portal_access', $request->teachers_portal_access);
        }
        Setting::set('teachers_portal_message', $request->teachers_portal_message ?? '');

        // Teacher feature permissions
        // Checkboxes only appear in POST when checked (ON).
        // If a key is absent from POST it means the admin unchecked it (OFF).
        // Default behaviour: if admin has never saved, lecturerCan() returns true.
        $permKeys = ['attendance', 'exams', 'reports', 'notifications', 'edit_profile'];
        foreach ($permKeys as $perm) {
            $value = $request->has('lecturer_can_' . $perm) ? 1 : 0;
            Setting::set('lecturer_can_' . $perm, $value);
        }

        // Report card orientation
        if ($request->filled('report_card_orientation')) {
            try {
                $siteSettings = \App\Models\Website\SiteSetting::instance();
                $siteSettings->update(['report_card_orientation' => $request->report_card_orientation]);
            } catch (\Throwable $e) {
                // Column may not exist yet — store in app settings as fallback
                Setting::set('report_card_orientation', $request->report_card_orientation);
            }
        }

        return back()->with('success', 'Portal settings saved successfully.');
    }
}
