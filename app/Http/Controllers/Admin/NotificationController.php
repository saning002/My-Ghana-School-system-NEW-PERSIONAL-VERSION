<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = StudentNotification::with(['sender', 'student.user', 'lecturer'])
            ->latest()->paginate(25);

        $students  = Student::with('user')->orderBy('id')->get();
        $lecturers = User::where('role', 'lecturer')->orderBy('full_name')->get();

        return view('admin.notifications.index', compact(
            'notifications', 'students', 'lecturers'
        ));
    }

    public function store(Request $request)
    {
        $recipientType = $request->input('recipient_type', 'student');

        $rules = [
            'recipient_type' => 'required|in:student,lecturer',
            'title'          => 'required|string|max:255',
            'message'        => 'required|string|max:3000',
            'type'           => 'required|in:general,fee,emergency,result,attendance',
            'audience'       => 'required|in:individual,all',
        ];

        if ($recipientType === 'student') {
            $rules['student_id'] = 'required_if:audience,individual|nullable|exists:students,id';
        } else {
            $rules['lecturer_id'] = 'required_if:audience,individual|nullable|exists:users,id';
        }

        $request->validate($rules);

        StudentNotification::create([
            'sent_by'        => auth()->id(),
            'recipient_type' => $recipientType,
            'student_id'     => ($recipientType === 'student' && $request->audience === 'individual')
                                    ? $request->student_id : null,
            'lecturer_id'    => ($recipientType === 'lecturer' && $request->audience === 'individual')
                                    ? $request->lecturer_id : null,
            'audience'       => $request->audience,
            'type'           => $request->type,
            'title'          => $request->title,
            'message'        => $request->message,
            'is_read'        => false,
        ]);

        $target = $recipientType === 'lecturer' ? 'teacher(s)' : 'student(s)';
        return back()->with('success', "Notification sent to {$target} successfully.");
    }

    public function destroy(StudentNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
