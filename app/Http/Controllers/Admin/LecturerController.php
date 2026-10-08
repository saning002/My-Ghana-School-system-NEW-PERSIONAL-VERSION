<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Program;
use App\Models\CourseAssignment;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class LecturerController extends Controller
{
    public function index()
    {
        $lecturers = User::where('role', 'lecturer')
            ->withCount('courseAssignments')
            ->latest()
            ->paginate(15);
        return view('admin.lecturers.index', compact('lecturers'));
    }

    public function create()
    {
        $programs = Program::with('courses')->orderBy('sequence')->get();
        return view('admin.lecturers.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other',
            'nationality'   => 'nullable|string|max:100',
            'qualification' => 'nullable|string|max:255',
            'bio'           => 'nullable|string|max:2000',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'assignments'   => 'nullable|array',  // assignments[course_id] = 1
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $this->uploadPhoto($request);
        }

        $lecturer = User::create([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'phone'         => $request->phone,
            'address'       => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender'        => $request->gender,
            'nationality'   => $request->nationality,
            'qualification' => $request->qualification,
            'bio'           => $request->bio,
            'photo'         => $photoPath,
            'role'          => 'lecturer',
        ]);

        $this->syncAssignments($lecturer, $request->input('assignments', []));

        return redirect()->route('admin.lecturers.show', $lecturer)
            ->with('success', 'Lecturer added successfully.');
    }

    public function show(User $lecturer)
    {
        $assignments = CourseAssignment::with(['course', 'program'])
            ->where('lecturer_id', $lecturer->id)
            ->get()
            ->groupBy('program_id');

        $programs = Program::orderBy('sequence')->get()->keyBy('id');

        return view('admin.lecturers.show', compact('lecturer', 'assignments', 'programs'));
    }

    public function edit(User $lecturer)
    {
        $programs = Program::with('courses')->orderBy('sequence')->get();

        $assignedCourseIds = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->pluck('course_id')
            ->toArray();

        return view('admin.lecturers.edit', compact('lecturer', 'programs', 'assignedCourseIds'));
    }

    public function update(Request $request, User $lecturer)
    {
        $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $lecturer->id,
            'phone'         => 'nullable|string|max:30',
            'address'       => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other',
            'nationality'   => 'nullable|string|max:100',
            'qualification' => 'nullable|string|max:255',
            'bio'           => 'nullable|string|max:2000',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'assignments'   => 'nullable|array',
        ]);

        $updates = $request->only([
            'full_name', 'email', 'phone', 'address',
            'date_of_birth', 'gender', 'nationality',
            'qualification', 'bio',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6']);
            $updates['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $newPath = $this->uploadPhoto($request);
            if ($newPath) {
                $this->deleteOldPhoto($lecturer->photo);
                $updates['photo'] = $newPath;
            }
        }

        $lecturer->update($updates);

        $this->syncAssignments($lecturer, $request->input('assignments', []));

        return redirect()->route('admin.lecturers.show', $lecturer)
            ->with('success', 'Lecturer updated successfully.');
    }

    public function destroy(User $lecturer)
    {
        $this->deleteOldPhoto($lecturer->photo);
        CourseAssignment::where('lecturer_id', $lecturer->id)->delete();
        $lecturer->delete();

        return redirect()->route('admin.lecturers.index')
            ->with('success', 'Lecturer removed successfully.');
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * Sync course assignments for a lecturer.
     * $assignments is an array of course_ids that are checked.
     */
    private function syncAssignments(User $lecturer, array $checkedCourseIds): void
    {
        // Remove all existing assignments
        CourseAssignment::where('lecturer_id', $lecturer->id)->delete();

        if (empty($checkedCourseIds)) return;

        $year = date('Y') . '/' . (date('Y') + 1);

        foreach ($checkedCourseIds as $courseId => $val) {
            $course = Course::with('program')->find($courseId);
            if (! $course) continue;

            CourseAssignment::create([
                'lecturer_id' => $lecturer->id,
                'course_id'   => $courseId,
                'program_id'  => $course->program_id,
                'year'        => $year,
            ]);
        }
    }

    private function uploadPhoto(Request $request): ?string
    {
        $file = $request->file('photo');
        try {
            if (CloudinaryService::isConfigured()) {
                $url = CloudinaryService::upload($file, 'lecturers/photos');
                if ($url) return $url;
            }
        } catch (\Throwable $e) {
            \Log::warning('Cloudinary upload failed for lecturer photo: ' . $e->getMessage());
        }

        try {
            $stored = Storage::disk('public')->putFile('lecturers/photos', $file);
            if ($stored) return $stored;
        } catch (\Throwable $e) {
            \Log::warning('Local storage upload failed for lecturer photo: ' . $e->getMessage());
        }

        return null;
    }

    private function deleteOldPhoto(?string $path): void
    {
        if (! $path) return;
        try {
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                CloudinaryService::delete($path);
            } elseif (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to delete old lecturer photo: ' . $e->getMessage());
        }
    }
}
