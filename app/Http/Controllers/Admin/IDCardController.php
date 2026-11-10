<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class IDCardController extends Controller
{
    /**
     * Show the ID card selection page.
     * Now supports students, teachers and admin staff.
     */
    public function index(Request $request)
    {
        $programs   = Program::orderBy('sequence')->get();
        $cardType   = $request->get('card_type', 'student'); // student | staff
        $programId  = $request->get('program_id');
        $search     = $request->get('search');

        $students        = collect();
        $staff           = collect();
        $incompleteCount = 0;

        if ($cardType === 'staff') {
            // Teachers + admins
            $query = User::whereIn('role', ['admin', 'lecturer', 'teacher', 'lecturers'])
                ->orWhere('role', 'like', '%admin%')
                ->orWhere('role', 'like', '%lecturer%')
                ->orWhere('role', 'like', '%teacher%');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'ilike', "%{$search}%")
                      ->orWhere('email', 'ilike', "%{$search}%");
                });
            }

            $staff = $query->orderBy('full_name')->paginate(50);

            foreach ($staff as $s) {
                if (!$s->full_name || !$s->photo) {
                    $incompleteCount++;
                }
            }
        } else {
            $query = Student::with(['user', 'program'])->where('status', 'active');

            if ($programId) {
                $query->where('program_id', $programId);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('user', fn($u) => $u->where('full_name', 'ilike', "%{$search}%"))
                      ->orWhere('student_id', 'ilike', "%{$search}%");
                });
            }

            $students = $query->orderBy('student_id')->paginate(50);

            foreach ($students as $s) {
                if (!$s->user?->full_name || !$s->student_id || !$s->photo) {
                    $incompleteCount++;
                }
            }
        }

        return view('admin.id-cards.index', compact(
            'students', 'staff', 'programs', 'programId',
            'search', 'incompleteCount', 'cardType'
        ));
    }

    /**
     * Render printable ID cards for students or staff.
     */
    public function print(Request $request)
    {
        $cardType   = $request->input('card_type', 'student');
        $ids        = $request->input('card_ids', []);

        if (empty($ids)) {
            return redirect()->route('admin.id-cards.index')
                ->with('error', 'Please select at least one person.');
        }

        // ── School info ───────────────────────────────────────────────────
        $schoolName    = \App\Models\Setting::get('school_name', config('app.name', 'School'));
        $schoolSubtitle = \App\Models\Setting::get('school_subtitle', '');
        $schoolAddress = \App\Models\Setting::get('school_address', '');
        $schoolPhone   = \App\Models\Setting::get('school_phone', '');
        $schoolEmail   = \App\Models\Setting::get('school_email', '');
        $siteLogoUrl   = \App\Models\Setting::get('site_logo_url', '');
        $schoolWebsite = request()->getSchemeAndHttpHost();

        // Headmaster signature (uploaded in Settings)
        $headmasterSignatureUrl = null;
        try {
            $sigPath = \App\Models\Setting::get('headmaster_signature', '');
            if ($sigPath) {
                // Try public disk first, fall back to direct asset URL
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath)) {
                    $headmasterSignatureUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($sigPath);
                } else {
                    // Fallback: construct URL from app URL
                    $headmasterSignatureUrl = rtrim(config('app.url'), '/') . '/storage/' . ltrim($sigPath, '/');
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Signature URL build failed: ' . $e->getMessage());
        }

        // Emergency phone (ID card specific — may differ from general school phone)
        $idCardPhone = \App\Models\Setting::get('id_card_emergency_phone', $schoolPhone);

        // ── Theme ─────────────────────────────────────────────────────────
        $primaryColor = '#D4A017';
        $navColor     = '#0B1121';
        try {
            $theme        = \App\Models\Website\SiteSetting::instance()->theme;
            $primaryColor = $theme['primary']  ?? $primaryColor;
            $navColor     = $theme['adminNav'] ?? $theme['secondary'] ?? $navColor;
        } catch (\Throwable $e) {}

        // ── Academic year ─────────────────────────────────────────────────
        $academicYear = date('Y');
        try {
            $period = \App\Models\AcademicPeriod::where('is_active', true)->first();
            if ($period) {
                $session = \App\Models\AcademicSession::find($period->academic_session_id ?? null);
                if ($session) $academicYear = $session->year;
            }
        } catch (\Throwable $e) {}

        // ── Build the people list ─────────────────────────────────────────
        $people = collect();

        if ($cardType === 'staff') {
            $users = User::whereIn('id', $ids)->orderBy('full_name')->get();
            foreach ($users as $u) {
                $people->push([
                    'type'       => 'staff',
                    'name'       => $u->full_name ?? 'Unknown',
                    'id_number'  => $u->id,
                    'role'       => ucfirst(str_replace('_', ' ', $u->role ?? 'Staff')),
                    'dept'       => null,
                    'dob'        => null,
                    'gender'     => $u->gender ?? null,
                    'phone'      => $u->phone ?? null,
                    'email'      => $u->email ?? null,
                    'photo'      => $u->photo ?? null,
                    'guardian'   => null,
                    'guardianPh' => null,
                    'qr_data'    => $schoolWebsite,
                ]);
            }
        } else {
            $students = Student::with(['user', 'program'])
                ->whereIn('id', $ids)
                ->where('status', 'active')
                ->orderBy('student_id')
                ->get();
            foreach ($students as $s) {
                $people->push([
                    'type'       => 'student',
                    'name'       => $s->user?->full_name ?? 'Unknown',
                    'id_number'  => $s->student_id ?? '—',
                    'role'       => 'Student',
                    'dept'       => $s->program?->name ?? '—',
                    'dob'        => $s->date_of_birth ? \Carbon\Carbon::parse($s->date_of_birth)->format('d M Y') : null,
                    'gender'     => $s->gender ?? null,
                    'phone'      => $s->guardian_phone ?? null,
                    'email'      => null,
                    'photo'      => $s->photo ?? null,
                    'guardian'   => $s->guardian_name ?? null,
                    'guardianPh' => $s->guardian_phone ?? null,
                    'qr_data'    => $schoolWebsite,
                ]);
            }
        }

        if ($people->isEmpty()) {
            return redirect()->route('admin.id-cards.index')
                ->with('error', 'No matching records found.');
        }

        return view('admin.id-cards.print', compact(
            'people', 'schoolName', 'schoolSubtitle', 'schoolAddress',
            'schoolPhone', 'schoolEmail', 'siteLogoUrl', 'schoolWebsite',
            'primaryColor', 'navColor', 'academicYear', 'cardType',
            'headmasterSignatureUrl', 'idCardPhone'
        ));
    }
}
