<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeeReminderService
{
    public function __construct(private TermFeeService $feeService) {}

    /**
     * Build a personalised fee reminder message for ONE student.
     * Every field is pulled from the student's real data.
     */
    public function buildMessage(Student $student, int $sessionId, string $template = 'default'): string
    {
        $summary     = $this->feeService->getStudentTermSummary($student, $sessionId);
        $session     = AcademicSession::find($sessionId);
        $schoolName  = Setting::get('school_name', 'School');
        $schoolPhone = Setting::get('school_phone', '');

        $studentName  = $student->user?->full_name ?? 'Student';
        $studentId    = $student->student_id ?? '';
        $program      = $student->program?->name ?? '';
        $totalBilled  = number_format($summary['total_billed'], 2);
        $totalPaid    = number_format($summary['total_paid'], 2);
        $balance      = number_format(max(0, $summary['balance']), 2);
        $sessionYear  = $session?->year ?? 'Current Term';

        if ($summary['balance'] <= 0) {
            // Paid in full — thank you message
            return "Dear Parent/Guardian of {$studentName} ({$studentId}),\n\n"
                . "We are pleased to inform you that all fees for {$sessionYear} have been fully paid.\n\n"
                . "Fee Summary:\n"
                . "  Total Billed: GHS {$totalBilled}\n"
                . "  Total Paid:   GHS {$totalPaid}\n"
                . "  Balance:      GHS 0.00\n\n"
                . "Thank you for your prompt payment.\n\n"
                . "— {$schoolName}";
        }

        // Owing message — personalised with exact amounts
        $breakdown = '';
        if ($summary['program_fee'] > 0) {
            $breakdown .= "  Programme Fee:  GHS " . number_format($summary['program_fee'], 2) . "\n";
        }
        if ($summary['exam_fee'] > 0) {
            $breakdown .= "  Exam Fee:       GHS " . number_format($summary['exam_fee'], 2) . "\n";
        }
        if ($summary['other_fees'] > 0) {
            $label = $summary['other_label'] ?? 'Other Fees';
            $breakdown .= "  {$label}: GHS " . number_format($summary['other_fees'], 2) . "\n";
        }

        $message = "Dear Parent/Guardian of {$studentName},\n\n"
            . "This is a fee reminder from {$schoolName}.\n\n"
            . "Student:  {$studentName} ({$studentId})\n"
            . "Programme: {$program}\n"
            . "Session:  {$sessionYear}\n\n"
            . "Fee Summary:\n"
            . $breakdown
            . "  ─────────────────────────\n"
            . "  Total Billed: GHS {$totalBilled}\n"
            . "  Total Paid:   GHS {$totalPaid}\n"
            . "  Outstanding:  GHS {$balance}\n\n"
            . "Please make payment at the school accounts office";

        if ($schoolPhone) {
            $message .= " or call us on {$schoolPhone}";
        }

        $message .= ".\n\nThank you.\n\n— {$schoolName}";

        return $message;
    }

    /**
     * Send reminder to one student via specified channels.
     * Checks credits before sending, deducts after success.
     * Returns array of results per channel.
     */
    public function sendReminder(Student $student, int $sessionId, array $channels, string $sentBy = 'Admin'): array
    {
        $message = $this->buildMessage($student, $sessionId);
        $summary = $this->feeService->getStudentTermSummary($student, $sessionId);
        $results = [];

        // Get tenant credits from central DB (always pgsql, never tenant DB)
        $tenantId = session('tenant_id');
        $credits  = null;
        if ($tenantId) {
            try {
                $credits = \App\Models\MessagingCredit::forTenant((int) $tenantId);
            } catch (\Throwable $e) {
                $credits = null;
            }
        }

        foreach ($channels as $channel) {
            // Check credits before sending
            if ($credits) {
                if ($channel === 'sms' && !$credits->hasSmsCredits()) {
                    $results[$channel] = ['status' => 'failed', 'error' => 'Insufficient SMS credits. Please top up.'];
                    $this->logReminder($student, $sessionId, $channel, 'N/A', $message, $summary['balance'], 'failed', 'Insufficient SMS credits', $sentBy);
                    continue;
                }
                if ($channel === 'whatsapp' && !$credits->hasWhatsappCredits()) {
                    $results[$channel] = ['status' => 'failed', 'error' => 'Insufficient WhatsApp credits. Please top up.'];
                    $this->logReminder($student, $sessionId, $channel, 'N/A', $message, $summary['balance'], 'failed', 'Insufficient WhatsApp credits', $sentBy);
                    continue;
                }
            }

            $recipient = match($channel) {
                'email'     => $student->user?->email ?? $student->guardian_email ?? null,
                'sms'       => $student->user?->phone ?? $student->guardian_phone ?? $student->phone ?? null,
                'whatsapp'  => $student->user?->phone ?? $student->guardian_phone ?? $student->phone ?? null,
                default     => null,
            };

            if (!$recipient) {
                $results[$channel] = ['status' => 'failed', 'error' => 'No ' . $channel . ' contact found'];
                $this->logReminder($student, $sessionId, $channel, $recipient ?? 'N/A', $message, $summary['balance'], 'failed', 'No contact found', $sentBy);
                continue;
            }

            $result = match($channel) {
                'email'    => $this->sendEmail($recipient, $student, $message, $sessionId),
                'sms'      => $this->sendSms($recipient, $message),
                'whatsapp' => $this->sendWhatsApp($recipient, $message),
                default    => ['status' => 'failed', 'error' => 'Unknown channel'],
            };

            // Deduct credits on success
            if ($result['status'] === 'sent' && $credits) {
                if ($channel === 'sms')      $credits->deductSms();
                if ($channel === 'whatsapp') $credits->deductWhatsapp();
            }

            $results[$channel] = $result;
            $this->logReminder(
                $student, $sessionId, $channel, $recipient, $message,
                $summary['balance'],
                $result['status'],
                $result['error'] ?? null,
                $sentBy
            );
        }

        return $results;
    }

    /**
     * Send reminders to multiple students.
     * Returns summary of sent/failed counts.
     */
    public function sendBulkReminders(array $studentIds, int $sessionId, array $channels, string $sentBy = 'Admin'): array
    {
        $sent   = 0;
        $failed = 0;
        $errors = [];

        foreach ($studentIds as $studentId) {
            $student = Student::with(['user', 'program'])->find($studentId);
            if (!$student) continue;

            $results = $this->sendReminder($student, $sessionId, $channels, $sentBy);

            foreach ($results as $channel => $result) {
                if ($result['status'] === 'sent') {
                    $sent++;
                } else {
                    $failed++;
                    $errors[] = "{$student->user?->full_name} ({$channel}): " . ($result['error'] ?? 'Failed');
                }
            }
        }

        return compact('sent', 'failed', 'errors');
    }

    // ─── SMS via Africa's Talking ───────────────────────────────────────────────

    private function sendSms(string $phone, string $message): array
    {
        $username = config('services.africastalking.username', env('AT_USERNAME', ''));
        $apiKey   = config('services.africastalking.api_key', env('AT_API_KEY', ''));
        $senderId = config('services.africastalking.sender_id', env('AT_SENDER_ID', ''));

        if (empty($username) || empty($apiKey)) {
            return ['status' => 'failed', 'error' => 'Africa\'s Talking credentials not configured'];
        }

        // Ensure phone has country code (+233 for Ghana)
        $phone = $this->formatGhanaPhone($phone);

        try {
            $url  = 'https://api.africastalking.com/version1/messaging';
            $data = [
                'username' => $username,
                'to'       => $phone,
                'message'  => $message,
            ];
            if ($senderId) {
                $data['from'] = $senderId;
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post($url, $data);

            $body = $response->json();

            if ($response->successful() && isset($body['SMSMessageData']['Recipients'][0]['status'])) {
                $status = $body['SMSMessageData']['Recipients'][0]['status'];
                if ($status === 'Success') {
                    return ['status' => 'sent'];
                }
                return ['status' => 'failed', 'error' => $status];
            }

            return ['status' => 'failed', 'error' => $response->body()];

        } catch (\Throwable $e) {
            Log::error('Africa\'s Talking SMS failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ─── WhatsApp via Twilio ────────────────────────────────────────────────────

    private function sendWhatsApp(string $phone, string $message): array
    {
        $sid   = config('services.twilio.sid', env('TWILIO_SID', ''));
        $token = config('services.twilio.token', env('TWILIO_TOKEN', ''));
        $from  = config('services.twilio.whatsapp_from', env('TWILIO_WHATSAPP_FROM', ''));

        if (empty($sid) || empty($token) || empty($from)) {
            return ['status' => 'failed', 'error' => 'Twilio WhatsApp credentials not configured'];
        }

        $phone = $this->formatGhanaPhone($phone);

        try {
            $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post($url, [
                    'From' => 'whatsapp:' . $from,
                    'To'   => 'whatsapp:' . $phone,
                    'Body' => $message,
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['sid'])) {
                return ['status' => 'sent', 'sid' => $body['sid']];
            }

            return ['status' => 'failed', 'error' => $body['message'] ?? $response->body()];

        } catch (\Throwable $e) {
            Log::error('Twilio WhatsApp failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ─── Email via Laravel Mail ─────────────────────────────────────────────────

    private function sendEmail(string $email, Student $student, string $message, int $sessionId): array
    {
        try {
            $schoolName = Setting::get('school_name', 'School');
            $studentName = $student->user?->full_name ?? 'Student';
            $session     = AcademicSession::find($sessionId);

            Mail::send([], [], function ($mail) use ($email, $message, $schoolName, $studentName, $session) {
                $mail->to($email, $studentName)
                     ->subject("Fee Reminder — {$schoolName}" . ($session ? " — {$session->year}" : ''))
                     ->html(nl2br(htmlspecialchars($message)));
            });

            return ['status' => 'sent'];

        } catch (\Throwable $e) {
            Log::error('Fee reminder email failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function formatGhanaPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);
        $phone = ltrim($phone, '+');

        // If starts with 0, replace with 233 (Ghana)
        if (str_starts_with($phone, '0')) {
            $phone = '233' . substr($phone, 1);
        }

        // If doesn't start with country code, add Ghana's
        if (!str_starts_with($phone, '233') && strlen($phone) === 9) {
            $phone = '233' . $phone;
        }

        return '+' . $phone;
    }

    private function logReminder(
        Student $student,
        int $sessionId,
        string $channel,
        string $recipient,
        string $message,
        float $amountOwed,
        string $status,
        ?string $error,
        string $sentBy
    ): void {
        try {
            DB::table('fee_reminders')->insert([
                'student_id'          => $student->id,
                'academic_session_id' => $sessionId,
                'channel'             => $channel,
                'recipient'           => $recipient,
                'recipient_name'      => $student->user?->full_name,
                'message'             => $message,
                'amount_owed'         => $amountOwed,
                'status'              => $status,
                'error_message'       => $error,
                'sent_by'             => $sentBy,
                'sent_at'             => $status === 'sent' ? now() : null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not log fee reminder: ' . $e->getMessage());
        }
    }
}
