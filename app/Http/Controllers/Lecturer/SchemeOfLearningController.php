<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Program;
use App\Models\SchemeOfLearning;
use Illuminate\Http\Request;

class SchemeOfLearningController extends Controller
{
    public function index(Request $request)
    {
        $lecturer   = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)->pluck('program_id')->unique();
        $programs   = Program::whereIn('id', $programIds)->orderBy('sequence')->get();

        $courses         = collect();
        $entries         = collect();
        $selectedProgram = null;
        $selectedCourse  = null;
        $selectedYear    = $request->get('academic_year', date('Y') . '/' . (date('Y') + 1));

        if ($request->filled('program_id')) {
            $assignedCourseIds = CourseAssignment::where('lecturer_id', $lecturer->id)
                ->where('program_id', $request->program_id)->pluck('course_id');
            $selectedProgram = $programs->find($request->program_id);
            $courses = Course::whereIn('id', $assignedCourseIds)->orderBy('name')->get();
        }

        if ($selectedProgram && $request->filled('course_id')) {
            $isAll = $request->course_id === 'all';
            $selectedCourse = $isAll ? null : $courses->find($request->course_id);

            $query = SchemeOfLearning::with(['course'])
                ->where('program_id', $selectedProgram->id)
                ->where('academic_year', $selectedYear);

            if ($isAll) {
                // Show all courses this lecturer is assigned to
                $assignedCourseIds = CourseAssignment::where('lecturer_id', $lecturer->id)
                    ->where('program_id', $selectedProgram->id)->pluck('course_id');
                $query->whereIn('course_id', $assignedCourseIds);
            } else {
                if (! $selectedCourse) goto skipEntries;
                $query->where('course_id', $selectedCourse->id);
            }

            $entries = $query->orderBy('course_id')->orderBy('week_number')->get();
            skipEntries:
        }

        $isAllCourses = $request->filled('course_id') && $request->course_id === 'all';

        $years = $this->academicYears();

        return view('lecturer.scheme-of-learning.index', compact(
            'programs','courses','entries',
            'selectedProgram','selectedCourse','selectedYear','years','isAllCourses'
        ));
    }

    public function create(Request $request)
    {
        $lecturer   = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)->pluck('program_id')->unique();
        $programs   = Program::whereIn('id', $programIds)->orderBy('sequence')->get();
        $courses    = $request->filled('program_id')
            ? Course::whereIn('id',
                CourseAssignment::where('lecturer_id', $lecturer->id)
                    ->where('program_id', $request->program_id)->pluck('course_id')
              )->orderBy('name')->get()
            : collect();
        $years = $this->academicYears();

        return view('lecturer.scheme-of-learning.form', compact('programs','courses','years'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['lecturer_id'] = auth()->id();
        SchemeOfLearning::create($data);
        return redirect()->route('lecturer.scheme-of-learning.index', [
            'program_id'    => $data['program_id'],
            'course_id'     => $data['course_id'],
            'academic_year' => $data['academic_year'],
        ])->with('success', 'Week entry added.');
    }

    public function edit(SchemeOfLearning $schemeOfLearning)
    {
        abort_if($schemeOfLearning->lecturer_id && $schemeOfLearning->lecturer_id !== auth()->id(), 403);
        $entry    = $schemeOfLearning;
        $lecturer = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)->pluck('program_id')->unique();
        $programs   = Program::whereIn('id', $programIds)->orderBy('sequence')->get();
        $courses    = Course::whereIn('id',
            CourseAssignment::where('lecturer_id', $lecturer->id)
                ->where('program_id', $entry->program_id)->pluck('course_id')
        )->orderBy('name')->get();
        $years = $this->academicYears();

        return view('lecturer.scheme-of-learning.form', compact('entry','programs','courses','years'));
    }

    public function update(Request $request, SchemeOfLearning $schemeOfLearning)
    {
        abort_if($schemeOfLearning->lecturer_id && $schemeOfLearning->lecturer_id !== auth()->id(), 403);
        $data = $this->validated($request);
        $schemeOfLearning->update($data);
        return redirect()->route('lecturer.scheme-of-learning.index', [
            'program_id'    => $data['program_id'],
            'course_id'     => $data['course_id'],
            'academic_year' => $data['academic_year'],
        ])->with('success', 'Entry updated.');
    }

    public function destroy(SchemeOfLearning $schemeOfLearning)
    {
        abort_if($schemeOfLearning->lecturer_id && $schemeOfLearning->lecturer_id !== auth()->id(), 403);

        // Clean up the PDF file (handles both Cloudinary URLs and disk paths)
        if ($schemeOfLearning->pdf_path) {
            \App\Services\StorageService::delete($schemeOfLearning->pdf_path);
        }

        $redirect = [
            'program_id'    => $schemeOfLearning->program_id,
            'course_id'     => $schemeOfLearning->course_id,
            'academic_year' => $schemeOfLearning->academic_year,
        ];
        $schemeOfLearning->delete();
        return redirect()->route('lecturer.scheme-of-learning.index', $redirect)->with('success', 'Entry deleted.');
    }

    public function coursesByProgram(Request $request)
    {
        $lecturer = auth()->user();
        $courses  = Course::whereIn('id',
            CourseAssignment::where('lecturer_id', $lecturer->id)
                ->where('program_id', $request->program_id)->pluck('course_id')
        )->orderBy('name')->get(['id','name','code']);
        return response()->json($courses);
    }

    /**
     * View or download the PDF attached to a scheme entry.
     * Lecturers can only access entries for courses assigned to them.
     */
    public function downloadPdf(\App\Models\SchemeOfLearning $schemeOfLearning, Request $request)
    {
        $lecturer = auth()->user();
        $assigned = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->where('course_id', $schemeOfLearning->course_id)
            ->exists();

        abort_if(! $assigned, 403, 'You are not assigned to this course.');
        abort_if(! $schemeOfLearning->pdf_path, 404, 'No PDF attached to this entry.');

        // Declare these first — used in both branches below
        $filename    = 'scheme_week' . $schemeOfLearning->week_number . '_' .
                       \Str::slug($schemeOfLearning->topic) . '.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        // External URL (Cloudinary or any HTTPS URL) — proxy through app, never expose URL
        if (filter_var($schemeOfLearning->pdf_path, FILTER_VALIDATE_URL)) {
            $tmp = \App\Services\StorageService::proxyFromUrl($schemeOfLearning->pdf_path);
            if ($tmp && file_exists($tmp)) {
                return response()->file($tmp, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
                ]);
            }
            // Proxy failed — last resort redirect (user sees Cloudinary URL but at least gets the file)
            return redirect()->away($schemeOfLearning->pdf_path);
        }

        // Local/B2/S3 path — stream through app
        $path = \App\Services\StorageService::localPath($schemeOfLearning->pdf_path);
        abort_if(! $path || ! file_exists($path), 404, 'PDF file not found.');

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $filename . '"',
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'program_id'         => 'required|exists:programs,id',
            'course_id'          => 'required|exists:courses,id',
            'academic_year'      => 'required|string|max:20',
            'term'               => 'nullable|string|max:30',
            'week_number'        => 'required|integer|min:1|max:52',
            'topic'              => 'required|string|max:255',
            'subtopics'          => 'nullable|string',
            'learning_objectives'=> 'nullable|string',
            'teaching_methods'   => 'nullable|string|max:255',
            'resources'          => 'nullable|string|max:255',
            'assessment_type'    => 'nullable|string|max:100',
            'remarks'            => 'nullable|string',
        ]);
    }

    private function academicYears(): array
    {
        $y = (int) date('Y');
        return [($y-1)."/$y", "$y/".($y+1), ($y+1)."/".($y+2)];
    }
}
