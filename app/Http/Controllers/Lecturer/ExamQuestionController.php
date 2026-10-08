<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\ExamQuestion;
use App\Services\StorageService;
use Illuminate\Http\Request;

class ExamQuestionController extends Controller
{
    public function index(Request $request)
    {
        $lecturer    = auth()->user();
        $assignments = CourseAssignment::with(['course', 'program'])
            ->where('lecturer_id', $lecturer->id)
            ->get();

        try {
            $q = ExamQuestion::with(['course', 'program'])
                ->where('lecturer_id', $lecturer->id)
                ->orderByDesc('created_at');

            if ($request->filled('course_id'))     { $q->where('course_id',     $request->course_id); }
            if ($request->filled('academic_year')) { $q->where('academic_year', $request->academic_year); }

            $documents = $q->paginate(20)->withQueryString();
        } catch (\Throwable $e) {
            \Log::warning('ExamQuestion (lecturer): ' . $e->getMessage());
            $documents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        $years = $this->academicYears();
        return view('lecturer.exams.questions', compact('documents', 'assignments', 'years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id'     => 'required|exists:courses,id',
            'title'         => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:20',
            'term'          => 'nullable|string|max:30',
            'notes'         => 'nullable|string',
            'document'      => 'required|file|mimes:pdf,doc,docx|max:20480',
        ]);

        $lecturer   = auth()->user();
        $assignment = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->where('course_id', $request->course_id)
            ->first();
        abort_unless($assignment, 403, 'You are not assigned to this course.');

        $file    = $request->file('document');
        $ext     = strtolower($file->getClientOriginalExtension());
        $docType = $ext === 'pdf' ? 'pdf' : 'word';

        // StorageService only — never Cloudinary for documents
        $path = StorageService::uploadDocument($file, 'exam-questions');

        if (! $path) {
            return back()->withErrors(['document' => 'Failed to upload the document. Please try again.']);
        }

        ExamQuestion::create([
            'lecturer_id'       => $lecturer->id,
            'course_id'         => $request->course_id,
            'program_id'        => $assignment->program_id,
            'church_branch_id'  => $lecturer->church_branch_id,
            'title'             => $request->title,
            'academic_year'     => $request->academic_year ?? (date('Y') . '/' . (date('Y') + 1)),
            'term'              => $request->term,
            'document_type'     => $docType,
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'file_size'         => $file->getSize(),
            'notes'             => $request->notes,
            'is_approved'       => false,
        ]);

        return redirect()->route('lecturer.exam-questions.index')
            ->with('success', 'Document uploaded successfully. Admin will review it shortly.');
    }

    public function download(ExamQuestion $examQuestion)
    {
        abort_unless($examQuestion->lecturer_id === auth()->id(), 403);

        // Cloudinary or any external URL — stream through app (proxy)
        if (filter_var($examQuestion->file_path, FILTER_VALIDATE_URL)) {
            $tmp = StorageService::proxyFromUrl($examQuestion->file_path);
            if ($tmp && file_exists($tmp)) {
                return response()->download($tmp, $examQuestion->original_filename);
            }
            return redirect()->away($examQuestion->file_path);
        }

        // Local / B2 / S3 path — stream via localPath()
        $localPath = StorageService::localPath($examQuestion->file_path);
        abort_unless($localPath && file_exists($localPath), 404, 'File not found.');
        return response()->download($localPath, $examQuestion->original_filename);
    }

    public function view(ExamQuestion $examQuestion)
    {
        abort_unless($examQuestion->lecturer_id === auth()->id(), 403);
        abort_unless($examQuestion->document_type === 'pdf', 400, 'Inline view only for PDF files.');

        // Cloudinary or any external URL — stream through app (proxy)
        if (filter_var($examQuestion->file_path, FILTER_VALIDATE_URL)) {
            $tmp = StorageService::proxyFromUrl($examQuestion->file_path);
            if ($tmp && file_exists($tmp)) {
                return response()->file($tmp, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . $examQuestion->original_filename . '"',
                ]);
            }
            return redirect()->away($examQuestion->file_path);
        }

        // Local / B2 / S3 path — stream via localPath()
        $localPath = StorageService::localPath($examQuestion->file_path);
        abort_unless($localPath && file_exists($localPath), 404, 'File not found.');
        return response()->file($localPath, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $examQuestion->original_filename . '"',
        ]);
    }

    public function destroy(ExamQuestion $examQuestion)
    {
        abort_unless($examQuestion->lecturer_id === auth()->id(), 403);

        // StorageService::delete() handles both Cloudinary URLs and disk paths
        if ($examQuestion->file_path) {
            StorageService::delete($examQuestion->file_path);
        }

        $examQuestion->delete();
        return back()->with('success', 'Document deleted.');
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
