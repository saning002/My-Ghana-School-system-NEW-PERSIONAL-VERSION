<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\CourseAssignment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        abort_unless(Setting::lecturerCan('notifications'), 403, 'Notifications have been disabled by the administrator.');
        $lecturer = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->pluck('program_id')
            ->unique()
            ->toArray();

        $students = Student::with('user')
            ->whereIn('program_id', $programIds)
            ->orderBy('student_id')
            ->get();

        // Notifications this lecturer sent to students
        $notifications = StudentNotification::with(['student.user'])
            ->where('sent_by', $lecturer->id)
            ->where(function ($q) {
                $q->where('recipient_type', 'student')->orWhereNull('recipient_type');
            })
            ->latest()
            ->paginate(20);

        // Messages from admin addressed to this lecturer
        $adminNotifications = StudentNotification::with(['sender'])
            ->where('recipient_type', 'lecturer')
            ->where(function ($q) use ($lecturer) {
                $q->where('audience', 'all')
                  ->orWhere(function ($q2) use ($lecturer) {
                      $q2->where('audience', 'individual')
                         ->where('lecturer_id', $lecturer->id);
                  });
            })
            ->latest()
            ->paginate(20);

        // Mark all unread admin messages as read
        StudentNotification::where('recipient_type', 'lecturer')
            ->where('is_read', false)
            ->where(function ($q) use ($lecturer) {
                $q->where('audience', 'all')
                  ->orWhere(function ($q2) use ($lecturer) {
                      $q2->where('audience', 'individual')
                         ->where('lecturer_id', $lecturer->id);
                  });
            })
            ->update(['is_read' => true]);

        return view('lecturer.notifications.index', compact('students', 'notifications', 'adminNotifications'));
    }

    public function store(Request $request)
    {
        abort_unless(Setting::lecturerCan('notifications'), 403, 'Notifications have been disabled by the administrator.');

        $lecturer = auth()->user();
        $programIds = CourseAssignment::where('lecturer_id', $lecturer->id)
            ->pluck('program_id')
            ->unique()
            ->toArray();

        $request->validate([
            'title'      => 'required|string|max:255',
            'message'    => 'required|string|max:2000',
            'type'       => 'required|in:general,fee,emergency,result,attendance',
            'audience'   => 'required|in:individual,all',
            'student_id' => 'required_if:audience,individual|nullable|exists:students,id',
        ]);

        if ($request->audience === 'individual' && $request->filled('student_id')) {
            $student = Student::find($request->student_id);
            if (! $student || ! in_array($student->program_id, $programIds, true)) {
                return back()->withErrors(['student_id' => 'Selected student is not assigned to your courses.']);
            }
        }

        StudentNotification::create([
            'sent_by'    => $lecturer->id,
            'student_id' => $request->audience === 'individual' ? $request->student_id : null,
            'audience'   => $request->audience,
            'type'       => $request->type,
            'title'      => $request->title,
            'message'    => $request->message,
            'is_read'    => false,
        ]);

        return back()->with('success', 'Notification sent successfully.');
    }
}
