<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use Illuminate\Http\Request;

class IDCardController extends Controller
{
    /**
     * Show the ID card selection page.
     */
    public function index(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();

        $query = Student::with(['user', 'program', 'churchBranch']);

        // Filter by programme
        $programId = $request->get('program_id');
        if ($programId) {
            $query->where('program_id', $programId);
        }

        // Search by name or ID
        $search = $request->get('search');
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%");
            })->orWhere('student_id', 'ilike', "%{$search}%");
        }

        // Only active students
        $query->where('status', 'active');

        $students = $query->orderBy('student_id')->paginate(50);

        // Check for incomplete profiles
        $incompleteCount = 0;
        foreach ($students as $s) {
            if (!$s->user?->full_name || !$s->student_id || !$s->photo) {
                $incompleteCount++;
            }
        }

        return view('admin.id-cards.index', compact(
            'students', 'programs', 'programId', 'search', 'incompleteCount'
        ));
    }

    /**
     * Render the printable ID cards.
     * Accepts student IDs as query param or POST.
     */
    public function print(Request $request)
    {
        $studentIds = $request->input('student_ids', []);

        if (empty($studentIds)) {
            return redirect()->route('admin.id-cards.index')
                ->with('error', 'Please select at least one student.');
        }

        $students = Student::with(['user', 'program', 'churchBranch'])
            ->whereIn('id', $studentIds)
            ->where('status', 'active')
            ->orderBy('student_id')
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('admin.id-cards.index')
                ->with('error', 'No active students found for the selected IDs.');
        }

        // Get school info from settings
        $schoolName    = \App\Models\Setting::get('school_name', config('app.name', 'School'));
        $schoolSubtitle = \App\Models\Setting::get('school_subtitle', '');
        $schoolAddress = \App\Models\Setting::get('school_address', '');
        $schoolPhone   = \App\Models\Setting::get('school_phone', '');
        $schoolEmail   = \App\Models\Setting::get('school_email', '');
        $siteLogoUrl   = \App\Models\Setting::get('site_logo_url', '');

        // Get theme colours
        $theme = [];
        try {
            $settings = \App\Models\Website\SiteSetting::instance();
            $theme = $settings->theme;
        } catch (\Throwable $e) {
            $theme = ['primary' => '#D4A017', 'secondary' => '#0B1121'];
        }
        $primaryColor = $theme['primary'] ?? '#D4A017';
        $navColor     = $theme['adminNav'] ?? $theme['secondary'] ?? '#0B1121';

        // Academic year
        $academicYear = '';
        try {
            $period = \App\Models\AcademicPeriod::where('is_active', true)->first();
            if ($period) {
                $session = \App\Models\AcademicSession::find($period->academic_session_id ?? null);
                $academicYear = $session ? $session->year : date('Y');
            }
        } catch (\Throwable $e) {}
        if (empty($academicYear)) {
            $academicYear = date('Y');
        }

        return view('admin.id-cards.print', compact(
            'students', 'schoolName', 'schoolSubtitle', 'schoolAddress',
            'schoolPhone', 'schoolEmail', 'siteLogoUrl',
            'primaryColor', 'navColor', 'academicYear'
        ));
    }
}
