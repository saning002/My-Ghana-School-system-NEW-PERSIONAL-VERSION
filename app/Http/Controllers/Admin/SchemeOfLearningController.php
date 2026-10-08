<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use App\Models\SchemeOfLearning;
use App\Models\User;
use Illuminate\Http\Request;

class SchemeOfLearningController extends Controller
{
    public function index(Request $request)
    {
        $user          = auth()->user();
        $isBranchAdmin = $user && $user->isBranchAdmin();
        $branchId      = $isBranchAdmin ? $user->church_branch_id : null;

        $programs = Program::orderBy('sequence')->get();
        $courses  = collect();
        $entries  = collect();

        $selectedProgram = null;
        $selectedCourse  = null;
        $selectedYear    = $request->get('academic_year', date('Y') . '/' . (date('Y') + 1));

        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
            $courses = Course::where('program_id', $request->program_id)->orderBy('name')->get();
        }

        if ($selectedProgram && $request->filled('course_id')) {
            $isAll = $request->course_id === 'all';
            $selectedCourse = $isAll ? null : Course::find($request->course_id);

            $query = SchemeOfLearning::with(['lecturer', 'course'])
                ->where('program_id', $selectedProgram->id)
                ->where('academic_year', $selectedYear);

            // "All Courses" — no course_id filter
            if (! $isAll) {
                $query->where('course_id', $selectedCourse->id);
            }

            // Branch admins only see entries for their branch (or entries with no branch set)
            if ($isBranchAdmin) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('church_branch_id', $branchId)
                      ->orWhereNull('church_branch_id');
                });
            }

            // Optional term filter
            if ($request->filled('term')) {
                $query->where('term', $request->term);
            }

            $entries = $query->orderBy('course_id')->orderBy('week_number')->get();
        }

        $years = $this->academicYears();

        // $selectedCourse is null when "All Courses" is selected
        $isAllCourses = $request->filled('course_id') && $request->course_id === 'all';

        return view('admin.scheme-of-learning.index', compact(
            'programs','courses','entries',
            'selectedProgram','selectedCourse','selectedYear','years','isAllCourses'
        ));
    }

    public function create(Request $request)
    {
        $user      = auth()->user();
        $programs  = Program::orderBy('sequence')->get();
        $courses   = $request->filled('program_id')
            ? Course::where('program_id', $request->program_id)->orderBy('name')->get()
            : collect();
        // Branch admins only see lecturers from their branch
        $lecturerQuery = User::where('role', 'lecturer')->orderBy('full_name');
        if ($user->isBranchAdmin()) {
            $lecturerQuery->where('church_branch_id', $user->church_branch_id);
        }
        $lecturers = $lecturerQuery->get();
        $years     = $this->academicYears();

        return view('admin.scheme-of-learning.form', compact('programs','courses','lecturers','years'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // Attach the branch of the currently logged-in admin
        $user = auth()->user();
        if ($user->isBranchAdmin()) {
            $data['church_branch_id'] = $user->church_branch_id;
        }

        // Handle PDF upload — StorageService ONLY (B2 → S3 → public disk)
        // Cloudinary is for images only, NOT documents/PDFs
        if ($request->hasFile('pdf_file')) {
            $path = \App\Services\StorageService::uploadDocument(
                $request->file('pdf_file'), 'scheme-pdfs'
            );
            if ($path) $data['pdf_path'] = $path;
        }

        SchemeOfLearning::create($data);

        // If submitted from the PDF upload panel, redirect back to PDF mode
        $mode = $request->input('_redirect_mode', 'entries');

        return redirect()->route('admin.scheme-of-learning.index', [
            'program_id'    => $data['program_id'],
            'course_id'     => $data['course_id'],
            'academic_year' => $data['academic_year'],
            'mode'          => $mode,
        ])->with('success', $mode === 'pdf' ? 'Scheme PDF uploaded successfully.' : 'Week entry added successfully.');
    }

    public function edit(SchemeOfLearning $schemeOfLearning)
    {
        $this->authorizeEntry($schemeOfLearning);

        $entry     = $schemeOfLearning;
        $programs  = Program::orderBy('sequence')->get();
        $courses   = Course::where('program_id', $entry->program_id)->orderBy('name')->get();
        $user      = auth()->user();
        $lecturerQuery = User::where('role', 'lecturer')->orderBy('full_name');
        if ($user->isBranchAdmin()) {
            $lecturerQuery->where('church_branch_id', $user->church_branch_id);
        }
        $lecturers = $lecturerQuery->get();
        $years     = $this->academicYears();

        return view('admin.scheme-of-learning.form', compact('entry','programs','courses','lecturers','years'));
    }

    public function update(Request $request, SchemeOfLearning $schemeOfLearning)
    {
        $this->authorizeEntry($schemeOfLearning);

        $data = $this->validated($request);

        // Handle PDF upload (new file replaces old) — StorageService only, NOT Cloudinary
        if ($request->hasFile('pdf_file')) {
            if ($schemeOfLearning->pdf_path) {
                \App\Services\StorageService::delete($schemeOfLearning->pdf_path);
            }
            $path = \App\Services\StorageService::uploadDocument($request->file('pdf_file'), 'scheme-pdfs');
            if ($path) $data['pdf_path'] = $path;
        }

        // Allow removing the existing PDF
        if ($request->boolean('remove_pdf')) {
            if ($schemeOfLearning->pdf_path) {
                \App\Services\StorageService::delete($schemeOfLearning->pdf_path);
            }
            $data['pdf_path'] = null;
        }

        $schemeOfLearning->update($data);
        return redirect()->route('admin.scheme-of-learning.index', [
            'program_id'    => $data['program_id'],
            'course_id'     => $data['course_id'],
            'academic_year' => $data['academic_year'],
        ])->with('success', 'Entry updated successfully.');
    }

    public function destroy(SchemeOfLearning $schemeOfLearning)
    {
        $this->authorizeEntry($schemeOfLearning);

        // Clean up PDF file — StorageService handles both Cloudinary URLs and disk paths
        if ($schemeOfLearning->pdf_path) {
            \App\Services\StorageService::delete($schemeOfLearning->pdf_path);
        }

        $redirect = [
            'program_id'    => $schemeOfLearning->program_id,
            'course_id'     => $schemeOfLearning->course_id,
            'academic_year' => $schemeOfLearning->academic_year,
        ];
        $schemeOfLearning->delete();
        return redirect()->route('admin.scheme-of-learning.index', $redirect)
            ->with('success', 'Entry deleted.');
    }

    /**
     * Serve / download a scheme PDF.
     * Supports ?download=1 to force download instead of inline view.
     */
    public function downloadPdf(SchemeOfLearning $schemeOfLearning, Request $request)
    {
        abort_if(! $schemeOfLearning->pdf_path, 404, 'No PDF attached to this entry.');

        $filename    = 'scheme_week' . $schemeOfLearning->week_number . '_' .
                       \Str::slug($schemeOfLearning->topic) . '.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        // External URL (Cloudinary or any HTTPS URL) — stream through app via StorageService
        if (filter_var($schemeOfLearning->pdf_path, FILTER_VALIDATE_URL)) {
            $tmp = \App\Services\StorageService::proxyFromUrl($schemeOfLearning->pdf_path);
            if ($tmp && file_exists($tmp)) {
                return response()->file($tmp, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
                ]);
            }
            return redirect()->away($schemeOfLearning->pdf_path);
        }

        // Local or B2/S3 path — use StorageService
        $path = \App\Services\StorageService::localPath($schemeOfLearning->pdf_path);
        abort_if(! $path || ! file_exists($path), 404, 'PDF file not found.');

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    // AJAX: return courses for a program
    public function coursesByProgram(Request $request)
    {
        $courses = Course::where('program_id', $request->program_id)
            ->orderBy('name')->get(['id','name','code']);
        return response()->json($courses);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    /** Branch admins can only touch entries belonging to their branch. */
    private function authorizeEntry(SchemeOfLearning $entry): void
    {
        $user = auth()->user();
        if ($user->isBranchAdmin() && $entry->church_branch_id && $entry->church_branch_id !== $user->church_branch_id) {
            abort(403, 'You do not have permission to modify this entry.');
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'program_id'         => 'required|exists:programs,id',
            'course_id'          => 'required|exists:courses,id',
            'lecturer_id'        => 'nullable|exists:users,id',
            'academic_year'      => 'required|string|max:20',
            'term'               => 'nullable|string|max:30',
            'week_number'        => 'required|integer|min:0|max:52', // 0 = full-scheme PDF entry
            'topic'              => 'required|string|max:255',
            'subtopics'          => 'nullable|string',
            'learning_objectives'=> 'nullable|string',
            'teaching_methods'   => 'nullable|string|max:255',
            'resources'          => 'nullable|string|max:255',
            'assessment_type'    => 'nullable|string|max:100',
            'remarks'            => 'nullable|string',
            'pdf_file'           => 'nullable|file|mimes:pdf|max:20480',
        ]);
    }

    private function academicYears(): array
    {
        $current = (int) date('Y');
        $years   = [];
        for ($y = $current - 1; $y <= $current + 2; $y++) {
            $years[] = $y . '/' . ($y + 1);
        }
        return $years;
    }
}
