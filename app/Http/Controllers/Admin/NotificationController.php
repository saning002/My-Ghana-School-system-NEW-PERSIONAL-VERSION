<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessagingCredit;
use App\Models\Student;
use App\Models\StudentNotification;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private SmsService $sms) {}

    public function index()
    {
        $notifications = StudentNotification::with(['sender', 'student.user', 'lecturer'])
            ->latest()->paginate(25);

        $students  = Student::with('user')->orderBy('id')->get();
        $lecturers = User::where('role', 'lecturer')->orderBy('full_name')->get();

        // Get SMS credit balance for this tenant
        $smsCredits   = 0;
        $smsAvailable = false;
        $tenantId     = session('tenant_id');
        if ($tenantId) {
            try {
                $credits      = MessagingCredit::forTenant((int) $tenantId);
                $smsCredits   = $credits->sms_credits;
                $smsAvailable = $smsCredits > 0 && $this->sms->isAvailable()['available'];
            } catch (\Throwable $e) {}
        }

        return view('admin.notifications.index', compact(
            'notifications', 'students', 'lecturers', 'smsCredits', 'smsAvailable'
        ));
    }

    public function store(Request $request)
    {
        $recipientType = $request->input('recipient_type', 'student');
        $channel       = $request->input('channel', 'in_app'); // in_app | sms | both

        $rules = [
            'recipient_type' => 'required|in:student,lecturer',
            'channel'        => 'required|in:in_app,sms,both',
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

        // ── Create in-app notification record ─────────────────────────────────
        $notification = StudentNotification::create([
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
            'channel'        => $channel,
            'is_read'        => false,
        ]);

        $successMsg  = '';
        $warningMsg  = '';

        // ── Send SMS if channel is 'sms' or 'both' ────────────────────────────
        if (in_array($channel, ['sms', 'both'])) {
            $smsResult = $this->sendSmsNotification(
                $notification,
                $request->message,
                $recipientType,
                $request->audience,
                $request->student_id ?? null,
                $request->lecturer_id ?? null
            );

            $notification->update([
                'sms_status'       => $smsResult['sent'] > 0 ? 'sent' : 'failed',
                'sms_sent_count'   => $smsResult['sent'],
                'sms_failed_count' => $smsResult['failed'],
                'sms_error'        => !empty($smsResult['errors'])
                    ? implode('; ', array_slice($smsResult['errors'], 0, 3))
                    : null,
            ]);

            if ($smsResult['sent'] > 0 && $smsResult['failed'] === 0) {
                $successMsg = "SMS sent to {$smsResult['sent']} recipient(s). ";
            } elseif ($smsResult['sent'] > 0) {
                $successMsg = "SMS sent to {$smsResult['sent']}, failed for {$smsResult['failed']}. ";
            } else {
                $warningMsg = "SMS failed: " . implode(', ', array_slice($smsResult['errors'], 0, 2)) . '. ';
            }
        }

        $target = $recipientType === 'lecturer' ? 'teacher(s)' : 'student(s)';
        $inAppMsg = $channel === 'sms' ? '' : "In-app notification sent to {$target}. ";

        if ($warningMsg) {
            return back()->with('success', $inAppMsg)->with('warning', $warningMsg);
        }

        return back()->with('success', $inAppMsg . $successMsg . "Done.");
    }

    public function destroy(StudentNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }

    // ── SMS dispatch ──────────────────────────────────────────────────────────

    private function sendSmsNotification(
        StudentNotification $notification,
        string $message,
        string $recipientType,
        string $audience,
        ?int $studentId,
        ?int $lecturerId
    ): array {
        $tenantId = session('tenant_id');
        $phones   = [];

        // Compose SMS message (title + body, keep under 160 chars if possible)
        $smsText = $notification->title . ': ' . $message;
        if (strlen($smsText) > 320) {
            $smsText = substr($smsText, 0, 317) . '...';
        }

        if ($recipientType === 'student') {
            if ($audience === 'individual' && $studentId) {
                $student = Student::with('user')->find($studentId);
                $phone   = $student?->user?->phone
                        ?? $student?->guardian_phone
                        ?? $student?->phone
                        ?? null;
                if ($phone) $phones[] = $phone;
            } else {
                // All students — get phone numbers from their user records or guardian
                Student::with('user')
                    ->whereNotIn('status', ['suspended', 'withdrawn'])
                    ->chunk(100, function ($students) use (&$phones) {
                        foreach ($students as $s) {
                            $phone = $s->user?->phone ?? $s->guardian_phone ?? $s->phone ?? null;
                            if ($phone) $phones[] = $phone;
                        }
                    });
            }
        } else {
            // Teachers
            if ($audience === 'individual' && $lecturerId) {
                $lecturer = User::find($lecturerId);
                if ($lecturer?->phone) $phones[] = $lecturer->phone;
            } else {
                User::where('role', 'lecturer')
                    ->whereNotNull('phone')
                    ->pluck('phone')
                    ->each(fn($p) => $phones[] = $p);
            }
        }

        if (empty($phones)) {
            return ['sent' => 0, 'failed' => 0, 'errors' => ['No phone numbers found for recipients.']];
        }

        // Remove duplicates and empty values
        $phones = array_unique(array_filter($phones));

        return $this->sms->sendBulk($phones, $smsText, $tenantId ? (int) $tenantId : null);
    }
}
