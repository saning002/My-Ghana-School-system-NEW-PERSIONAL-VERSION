<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessagingCredit;
use App\Models\NotificationTemplate;
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

        $templates = collect();
        try {
            $templates = NotificationTemplate::active()
                ->orderBy('category')->orderBy('name')
                ->get(['id', 'name', 'category', 'title', 'body']);
        } catch (\Throwable $e) {}

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
            'notifications', 'students', 'lecturers',
            'smsCredits', 'smsAvailable', 'templates'
        ));
    }

    public function store(Request $request)
    {
        $recipientType = $request->input('recipient_type', 'student');
        $channel       = $request->input('channel', 'in_app');

        $rules = [
            'recipient_type' => 'required|in:student,lecturer,parents',
            'channel'        => 'required|in:in_app,sms,both',
            'title'          => 'required|string|max:255',
            'message'        => 'required|string|max:3000',
            'type'           => 'required|in:general,fee,emergency,result,attendance',
            'audience'       => 'required|in:individual,all',
        ];

        if ($recipientType === 'student') {
            $rules['student_id'] = 'required_if:audience,individual|nullable|exists:students,id';
        } elseif ($recipientType === 'lecturer') {
            $rules['lecturer_id'] = 'required_if:audience,individual|nullable|exists:users,id';
        }

        $request->validate($rules);

        // ── Resolve placeholders in message text ──────────────────────────────
        // For individual student messages, replace {student_name}, {fee_balance} etc
        $rawMessage = $request->message;
        $rawTitle   = $request->title;
        $resolvedMessage = $rawMessage;
        $resolvedTitle   = $rawTitle;

        if ($recipientType === 'student' && $request->audience === 'individual' && $request->filled('student_id')) {
            try {
                $targetStudent = Student::with(['user', 'program', 'payments', 'attendances'])->find($request->student_id);
                if ($targetStudent) {
                    $resolvedMessage = NotificationTemplate::resolvePlaceholders($rawMessage, $targetStudent);
                    // Also resolve title if it has placeholders
                    if (str_contains($rawTitle, '{')) {
                        $resolvedTitle = NotificationTemplate::resolvePlaceholders($rawTitle, $targetStudent);
                    }
                }
            } catch (\Throwable $e) {
                // Placeholder resolution failed — use raw text, never block the send
            }
        }

        // ── Parents recipient: SMS-only (no portal account) ───────────────────
        if ($recipientType === 'parents') {
            // Force SMS channel for parents — they have no portal account
            $channel = in_array($channel, ['sms', 'both']) ? 'sms' : 'sms';

            $notification = StudentNotification::create([
                'sent_by'        => auth()->id(),
                'recipient_type' => 'parents',
                'student_id'     => null,
                'lecturer_id'    => null,
                'audience'       => 'all',
                'type'           => $request->type,
                'title'          => $resolvedTitle,
                'message'        => $rawMessage, // store raw for log
                'channel'        => 'sms',
                'is_read'        => false,
            ]);

            $smsResult = $this->sendParentsSms($notification, $rawMessage, $request->student_id ?? null);

            $notification->update([
                'sms_status'       => $smsResult['sent'] > 0 ? 'sent' : 'failed',
                'sms_sent_count'   => $smsResult['sent'],
                'sms_failed_count' => $smsResult['failed'],
                'sms_error'        => !empty($smsResult['errors'])
                    ? implode('; ', array_slice($smsResult['errors'], 0, 3))
                    : null,
            ]);

            if ($smsResult['sent'] > 0) {
                return back()->with('success', "SMS sent to {$smsResult['sent']} parent(s). " . ($smsResult['failed'] > 0 ? "{$smsResult['failed']} failed." : "Done."));
            }
            return back()->with('warning', 'SMS to parents failed: ' . implode(', ', array_slice($smsResult['errors'] ?? [], 0, 2)));
        }

        // ── Standard student / lecturer notification ──────────────────────────

        // Detect if the message/title contain any placeholders
        $hasPlaceholders = str_contains($rawMessage, '{') || str_contains($rawTitle, '{');

        // For in-app to ALL STUDENTS with placeholders: create one record per student
        // so each student sees their own resolved message (e.g. their own {student_name})
        if (
            $recipientType === 'student'
            && $request->audience === 'all'
            && in_array($channel, ['in_app', 'both'])
            && $hasPlaceholders
        ) {
            $sentBy   = auth()->id();
            $type     = $request->type;
            $baseData = [
                'sent_by'        => $sentBy,
                'recipient_type' => 'student',
                'audience'       => 'individual',
                'type'           => $type,
                'channel'        => $channel,
                'is_read'        => false,
            ];

            $count = 0;
            Student::with(['user', 'program', 'payments', 'attendances'])
                ->whereNotIn('status', ['withdrawn', 'suspended'])
                ->chunk(100, function ($students) use ($baseData, $rawTitle, $rawMessage, &$count) {
                    $rows = [];
                    $now  = now();
                    foreach ($students as $student) {
                        try {
                            $msg = NotificationTemplate::resolvePlaceholders($rawMessage, $student);
                            $ttl = str_contains($rawTitle, '{')
                                ? NotificationTemplate::resolvePlaceholders($rawTitle, $student)
                                : $rawTitle;
                        } catch (\Throwable $e) {
                            $msg = $rawMessage;
                            $ttl = $rawTitle;
                        }
                        $rows[] = array_merge($baseData, [
                            'student_id' => $student->id,
                            'title'      => $ttl,
                            'message'    => $msg,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                        $count++;
                    }
                    if ($rows) {
                        \Illuminate\Support\Facades\DB::connection('tenant')->table('student_notifications')->insert($rows);
                    }
                });

            // If channel includes SMS, still send bulk SMS (raw — can't personalise and also send per-record)
            $successMsg = '';
            $warningMsg = '';
            if (in_array($channel, ['sms', 'both'])) {
                // Create a single log record for the SMS part
                $logRecord = StudentNotification::create(array_merge($baseData, [
                    'student_id' => null,
                    'audience'   => 'all',
                    'title'      => $rawTitle,
                    'message'    => $rawMessage,
                    'channel'    => 'sms',
                ]));
                $smsResult = $this->sendSmsNotification($logRecord, $rawMessage, 'student', 'all', null, null);
                $logRecord->update([
                    'sms_status'       => $smsResult['sent'] > 0 ? 'sent' : 'failed',
                    'sms_sent_count'   => $smsResult['sent'],
                    'sms_failed_count' => $smsResult['failed'],
                    'sms_error'        => !empty($smsResult['errors']) ? implode('; ', array_slice($smsResult['errors'], 0, 3)) : null,
                ]);
                if ($smsResult['sent'] > 0) {
                    $successMsg = "SMS sent to {$smsResult['sent']} student(s). ";
                } else {
                    $warningMsg = "SMS failed: " . implode(', ', array_slice($smsResult['errors'], 0, 2)) . '. ';
                }
            }

            $inAppMsg = $channel === 'sms' ? '' : "In-app notification sent to {$count} student(s) with personalised content. ";
            if ($warningMsg) {
                return back()->with('success', $inAppMsg)->with('warning', $warningMsg);
            }
            return back()->with('success', $inAppMsg . $successMsg . "Done.");
        }

        // ── Default path: single broadcast record (no placeholders, or lecturers) ──
        $notification = StudentNotification::create([
            'sent_by'        => auth()->id(),
            'recipient_type' => $recipientType,
            'student_id'     => ($recipientType === 'student' && $request->audience === 'individual')
                                    ? $request->student_id : null,
            'lecturer_id'    => ($recipientType === 'lecturer' && $request->audience === 'individual')
                                    ? $request->lecturer_id : null,
            'audience'       => $request->audience,
            'type'           => $request->type,
            'title'          => $resolvedTitle,
            'message'        => $resolvedMessage,
            'channel'        => $channel,
            'is_read'        => false,
        ]);

        $successMsg = '';
        $warningMsg = '';

        if (in_array($channel, ['sms', 'both'])) {
            // For individual student SMS, send resolved message; for bulk send raw (can't personalize per-recipient)
            $smsMessage = ($recipientType === 'student' && $request->audience === 'individual')
                ? $resolvedMessage
                : $rawMessage;

            $smsResult = $this->sendSmsNotification(
                $notification,
                $smsMessage,
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

        $target   = $recipientType === 'lecturer' ? 'teacher(s)' : 'student(s)';
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

    /**
     * Bulk SMS broadcast from the Settings → SMS tab.
     */
    public function broadcastSms(Request $request)
    {
        $request->validate([
            'broadcast_target'     => 'required|in:all_parents,all_students,by_program,all_teachers',
            'broadcast_message'    => 'required|string|max:320',
            'broadcast_program_id' => 'nullable|exists:programs,id',
        ]);

        $target   = $request->broadcast_target;
        $message  = trim($request->broadcast_message);
        $tenantId = session('tenant_id') ? (int) session('tenant_id') : null;
        $phones   = [];

        switch ($target) {
            case 'all_parents':
                Student::whereNotIn('status', ['withdrawn', 'suspended'])
                    ->chunk(200, function ($students) use (&$phones) {
                        foreach ($students as $s) {
                            $phone = $s->guardian_phone ?? $s->mother_phone ?? $s->father_phone ?? null;
                            if ($phone) $phones[] = $phone;
                        }
                    });
                break;

            case 'all_students':
                Student::with('user')
                    ->whereNotIn('status', ['withdrawn', 'suspended'])
                    ->chunk(200, function ($students) use (&$phones) {
                        foreach ($students as $s) {
                            $phone = $s->user?->phone ?? $s->phone ?? null;
                            if ($phone) $phones[] = $phone;
                        }
                    });
                break;

            case 'by_program':
                if (!$request->filled('broadcast_program_id')) {
                    return back()->with('error', 'Please select a programme.')->withInput();
                }
                Student::with('user')
                    ->where('program_id', $request->broadcast_program_id)
                    ->whereNotIn('status', ['withdrawn', 'suspended'])
                    ->chunk(200, function ($students) use (&$phones) {
                        foreach ($students as $s) {
                            $phone = $s->guardian_phone ?? $s->user?->phone ?? $s->phone ?? null;
                            if ($phone) $phones[] = $phone;
                        }
                    });
                break;

            case 'all_teachers':
                User::where('role', 'lecturer')
                    ->whereNotNull('phone')
                    ->pluck('phone')
                    ->each(fn($p) => $phones[] = $p);
                break;
        }

        $phones = array_unique(array_filter($phones));

        if (empty($phones)) {
            return back()->with('error', 'No phone numbers found for the selected recipients. Make sure phone numbers are saved on student/teacher profiles.')->withInput();
        }

        $result = $this->sms->sendBulk($phones, $message, $tenantId);

        StudentNotification::create([
            'sent_by'          => auth()->id(),
            'recipient_type'   => in_array($target, ['all_teachers']) ? 'lecturer' : 'student',
            'audience'         => 'all',
            'type'             => 'general',
            'title'            => 'SMS Broadcast',
            'message'          => $message,
            'channel'          => 'sms',
            'is_read'          => false,
            'sms_status'       => $result['sent'] > 0 ? 'sent' : 'failed',
            'sms_sent_count'   => $result['sent'],
            'sms_failed_count' => $result['failed'],
            'sms_error'        => !empty($result['errors'])
                ? implode('; ', array_slice($result['errors'], 0, 3))
                : null,
        ]);

        if ($result['sent'] > 0 && $result['failed'] === 0) {
            return back()->with('success', "Broadcast sent to {$result['sent']} recipient(s) successfully.");
        } elseif ($result['sent'] > 0) {
            return back()->with('success', "Broadcast sent to {$result['sent']} recipient(s). {$result['failed']} failed.");
        }

        $errSample = implode(', ', array_slice($result['errors'], 0, 2));
        return back()->with('error', "Broadcast failed for all {$result['failed']} recipient(s). First errors: {$errSample}");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Send SMS to all parents/guardians of active students.
     * Personalises per-student if the message contains placeholders.
     */
    private function sendParentsSms(StudentNotification $notification, string $message, ?int $singleStudentId): array
    {
        $tenantId     = session('tenant_id');
        $hasPlaceholders = str_contains($message, '{');
        $sent = $failed = 0;
        $errors = [];

        $credits = null;
        if ($tenantId) {
            try { $credits = MessagingCredit::forTenant((int) $tenantId); } catch (\Throwable $e) {}
        }

        $query = Student::with(['user', 'program', 'payments', 'attendances'])
            ->whereNotIn('status', ['withdrawn', 'suspended']);

        $query->chunk(100, function ($students) use (
            $message, $hasPlaceholders, $credits, $notification, &$sent, &$failed, &$errors, $tenantId
        ) {
            foreach ($students as $student) {
                $phone = $student->guardian_phone
                    ?? $student->mother_phone
                    ?? $student->father_phone
                    ?? null;

                if (!$phone) {
                    $failed++;
                    continue;
                }

                // Personalise per student if placeholders used
                $smsText = $hasPlaceholders
                    ? NotificationTemplate::resolvePlaceholders($notification->title . ': ' . $message, $student)
                    : ($notification->title . ': ' . $message);

                if ($credits && !$credits->hasSmsCredits()) {
                    $failed++;
                    $errors[] = 'Insufficient SMS credits';
                    break;
                }

                $result = $this->sms->send($phone, $smsText);
                if ($result['status'] === 'sent') {
                    $sent++;
                    $credits?->deductSms();
                } else {
                    $failed++;
                    $errors[] = $phone . ': ' . ($result['error'] ?? 'failed');
                }
            }
        });

        return compact('sent', 'failed', 'errors');
    }

    /**
     * Send SMS to students or lecturers.
     */
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

        $phones = array_unique(array_filter($phones));
        return $this->sms->sendBulk($phones, $smsText, $tenantId ? (int) $tenantId : null);
    }
}
