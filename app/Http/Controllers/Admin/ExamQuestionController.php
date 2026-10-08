<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamQuestion;
use App\Models\Program;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    public function index(Request $request)
    {
        $user          = auth()->user();
        $isBranchAdmin = $user && $user->isBranchAdmin();
        $branchId      = $isBranchAdmin ? $user->church_branch_id : null;

        $programs  = Program::orderBy('sequence')->get();
        $courses   = $request->filled('program_id')
            ? Course::where('program_id', $request->program_id)->orderBy('name')->get()
            : collect();

        $lecturers = User::whereIn('role', ['lecturer', 'teacher'])
            ->when($branchId, fn($q) => $q->where('church_branch_id', $branchId))
            ->orderBy('full_name')->get();

        try {
            $q = ExamQuestion::with(['lecturer', 'course', 'program'])->orderByDesc('created_at');

            if ($isBranchAdmin) {
                $q->where(function ($query) use ($branchId) {
                    $query->where('church_branch_id', $branchId)->orWhereNull('church_branch_id');
                });
            }

            if ($request->filled('program_id'))    { $q->where('program_id',    $request->program_id); }
            if ($request->filled('course_id'))     { $q->where('course_id',     $request->course_id); }
            if ($request->filled('lecturer_id'))   { $q->where('lecturer_id',   $request->lecturer_id); }
            if ($request->filled('academic_year')) { $q->where('academic_year', $request->academic_year); }
            if ($request->filled('term'))          { $q->where('term',          $request->term); }
            if ($request->filled('document_type')) { $q->where('document_type', $request->document_type); }

            $documents = $q->paginate(25)->withQueryString();
        } catch (\Throwable $e) {
            \Log::warning('ExamQuestion table error: ' . $e->getMessage());
            $documents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
        }

        $years = $this->academicYears();
        return view('admin.exams.questions', compact('documents', 'programs', 'courses', 'lecturers', 'years'));
    }

    public function approve(ExamQuestion $examQuestion)
    {
        $examQuestion->update(['is_approved' => ! $examQuestion->is_approved]);
        $status = $examQuestion->is_approved ? 'approved' : 'unapproved';
        return back()->with('success', "Document marked as {$status}.");
    }

    public function download(ExamQuestion $examQuestion)
    {
        // External URL (legacy Cloudinary) — stream through app
        if (filter_var($examQuestion->file_path, FILTER_VALIDATE_URL)) {
            $tmp = \App\Services\StorageService::proxyFromUrl($examQuestion->file_path);
            if ($tmp && file_exists($tmp)) {
                return response()->download($tmp, $examQuestion->original_filename);
            }
            return redirect()->away($examQuestion->file_path);
        }
        $localPath = StorageService::localPath($examQuestion->file_path);
        abort_unless($localPath && file_exists($localPath), 404, 'File not found.');
        return response()->download($localPath, $examQuestion->original_filename);
    }

    public function view(ExamQuestion $examQuestion)
    {
        abort_unless($examQuestion->document_type === 'pdf', 400, 'Inline view only available for PDF files.');

        // External URL (legacy Cloudinary) — stream through app
        if (filter_var($examQuestion->file_path, FILTER_VALIDATE_URL)) {
            $tmp = \App\Services\StorageService::proxyFromUrl($examQuestion->file_path);
            if ($tmp && file_exists($tmp)) {
                return response()->file($tmp, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $examQuestion->original_filename . '"',
                ]);
            }
            return redirect()->away($examQuestion->file_path);
        }
        $localPath = StorageService::localPath($examQuestion->file_path);
        abort_unless($localPath && file_exists($localPath), 404, 'File not found.');
        return response()->file($localPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $examQuestion->original_filename . '"',
        ]);
    }

    public function destroy(ExamQuestion $examQuestion)
    {
        // StorageService::delete() handles both Cloudinary URLs and disk paths
        if ($examQuestion->file_path) {
            StorageService::delete($examQuestion->file_path);
        }
        $examQuestion->delete();
        return back()->with('success', 'Document deleted.');
    }

    public function comment(Request $request, ExamQuestion $examQuestion)
    {
        $request->validate(['comment' => 'required|string|max:1000']);
        $existing = $examQuestion->notes ?? '';
        $newNote  = $existing
            ? $existing . "\n\n[Admin — " . now()->format('M d, Y') . "]: " . $request->comment
            : "[Admin — " . now()->format('M d, Y') . "]: " . $request->comment;
        $examQuestion->update(['notes' => $newNote]);
        return back()->with('success', 'Comment added.');
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
