<?php

namespace App\Services;

use App\Models\MessagingCredit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsService — sends SMS via Arkesel (primary) or Africa's Talking (fallback).
 * Checks and deducts credits from the tenant's MessagingCredit record.
 */
class SmsService
{
    /**
     * Send an SMS to a single phone number.
     * Returns ['status'=>'sent'|'failed', 'error'=>'...']
     */
    public function send(string $phone, string $message): array
    {
        $phone = $this->formatGhanaPhone($phone);

        if (!empty(env('ARKESEL_API_KEY', ''))) {
            return $this->sendArkesel($phone, $message);
        }

        if (!empty(env('AT_USERNAME', ''))) {
            return $this->sendAfricasTalking($phone, $message);
        }

        return ['status' => 'failed', 'error' => 'No SMS provider configured. Set ARKESEL_API_KEY or AT_USERNAME in environment.'];
    }

    /**
     * Send SMS to multiple phone numbers.
     * Returns ['sent'=>n, 'failed'=>n, 'errors'=>[...]]
     */
    public function sendBulk(array $phones, string $message, ?int $tenantId = null, ?int $branchId = null): array
    {
        $sent   = 0;
        $failed = 0;
        $errors = [];

        // Get the right credit pool — branch-specific if available, else shared
        $credits = null;
        if ($tenantId) {
            $credits = $branchId
                ? MessagingCredit::forBranch($tenantId, $branchId)
                : MessagingCredit::forTenant($tenantId);
        }

        foreach ($phones as $phone) {
            if (!$phone) {
                $failed++;
                $errors[] = "Empty phone number skipped";
                continue;
            }

            // Check credits
            if ($credits && !$credits->hasSmsCredits()) {
                $failed++;
                $errors[] = "Insufficient SMS credits";
                break; // Stop if no more credits
            }

            $result = $this->send($phone, $message);

            if ($result['status'] === 'sent') {
                $sent++;
                if ($credits) {
                    $credits->deductSms();
                }
            } else {
                $failed++;
                $errors[] = "{$phone}: " . ($result['error'] ?? 'Failed');
            }
        }

        return compact('sent', 'failed', 'errors');
    }

    /**
     * Check if SMS is configured and credits are available.
     */
    public function isAvailable(?int $tenantId = null): array
    {
        $configured = !empty(env('ARKESEL_API_KEY', '')) || !empty(env('AT_USERNAME', ''));

        if (!$configured) {
            return ['available' => false, 'reason' => 'No SMS provider configured'];
        }

        if ($tenantId) {
            $credits = MessagingCredit::forTenant($tenantId);
            if (!$credits->hasSmsCredits()) {
                return [
                    'available' => false,
                    'reason'    => 'No SMS credits remaining',
                    'credits'   => $credits->sms_credits,
                ];
            }
            return ['available' => true, 'credits' => $credits->sms_credits];
        }

        return ['available' => true];
    }

    // ── Private: Arkesel ──────────────────────────────────────────────────────

    private function sendArkesel(string $phone, string $message): array
    {
        $apiKey   = env('ARKESEL_API_KEY', '');
        $senderId = env('ARKESEL_SENDER_ID', 'SchoolAlert');

        try {
            $response = Http::withHeaders(['api-key' => $apiKey])
                ->post('https://sms.arkesel.com/api/v2/sms/send', [
                    'sender'     => $senderId,
                    'message'    => $message,
                    'recipients' => [$phone],
                ]);

            $body = $response->json();

            if ($response->successful() && ($body['status'] ?? '') === 'success') {
                $recipients = $body['data'] ?? [];
                if (!empty($recipients) && strtolower($recipients[0]['status'] ?? '') === 'failed') {
                    return ['status' => 'failed', 'error' => 'Arkesel: ' . ($recipients[0]['reason'] ?? 'delivery failed')];
                }
                return ['status' => 'sent', 'to' => $phone];
            }

            $err = $body['message'] ?? ($body['error'] ?? $response->body());
            return ['status' => 'failed', 'error' => 'Arkesel: ' . $err];

        } catch (\Throwable $e) {
            Log::error('Arkesel SMS failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ── Private: Africa's Talking ─────────────────────────────────────────────

    private function sendAfricasTalking(string $phone, string $message): array
    {
        $username = env('AT_USERNAME', '');
        $apiKey   = env('AT_API_KEY', '');
        $senderId = env('AT_SENDER_ID', '');

        try {
            $data = ['username' => $username, 'to' => $phone, 'message' => $message];
            if ($senderId) $data['from'] = $senderId;

            $response = Http::withHeaders(['apiKey' => $apiKey, 'Accept' => 'application/json'])
                ->asForm()
                ->post('https://api.africastalking.com/version1/messaging', $data);

            $body = $response->json();

            if ($response->successful() && isset($body['SMSMessageData']['Recipients'][0]['status'])) {
                $status = $body['SMSMessageData']['Recipients'][0]['status'];
                if ($status === 'Success') return ['status' => 'sent'];
                return ['status' => 'failed', 'error' => $status];
            }

            return ['status' => 'failed', 'error' => $response->body()];

        } catch (\Throwable $e) {
            Log::error('AT SMS failed: ' . $e->getMessage());
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    public function formatGhanaPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            $phone = '+233' . substr($phone, 1);
        } elseif (str_starts_with($phone, '233') && !str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }
}
