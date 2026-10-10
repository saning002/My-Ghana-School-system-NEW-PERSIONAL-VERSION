<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey = config('paystack.secret_key', env('PAYSTACK_SECRET_KEY', ''));
    }

    /**
     * Initialize a Paystack transaction.
     *
     * @param  string  $email       Customer email
     * @param  float   $amount      Amount in GHS (converted to pesewas internally)
     * @param  string  $reference   Unique transaction reference
     * @param  string  $callbackUrl URL Paystack redirects to after payment
     * @param  array   $metadata    Extra data to attach to the transaction
     * @return array   ['status' => bool, 'authorization_url' => string, 'reference' => string, 'message' => string]
     */
    public function initializeTransaction(
        string $email,
        float  $amount,
        string $reference,
        string $callbackUrl,
        array  $metadata = []
    ): array {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transaction/initialize", [
                    'email'        => $email,
                    'amount'       => (int) round($amount * 100), // GHS to pesewas
                    'reference'    => $reference,
                    'callback_url' => $callbackUrl,
                    'currency'     => 'GHS',
                    'metadata'     => $metadata,
                ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? false)) {
                return [
                    'status'            => true,
                    'authorization_url' => $data['data']['authorization_url'],
                    'access_code'       => $data['data']['access_code'],
                    'reference'         => $data['data']['reference'],
                    'message'           => 'Transaction initialized',
                ];
            }

            return [
                'status'  => false,
                'message' => $data['message'] ?? 'Failed to initialize transaction',
            ];
        } catch (\Throwable $e) {
            Log::error('Paystack initializeTransaction failed: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Connection error: ' . $e->getMessage()];
        }
    }

    /**
     * Verify a Paystack transaction by reference.
     *
     * @return array ['status' => bool, 'paid' => bool, 'amount' => float (GHS), 'data' => array]
     */
    public function verifyTransaction(string $reference): array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/transaction/verify/{$reference}");

            $data = $response->json();

            if (!$response->successful() || !($data['status'] ?? false)) {
                return [
                    'status'  => false,
                    'paid'    => false,
                    'message' => $data['message'] ?? 'Verification failed',
                ];
            }

            $txData = $data['data'];
            $paid   = $txData['status'] === 'success';

            return [
                'status'   => true,
                'paid'     => $paid,
                'amount'   => $txData['amount'] / 100, // pesewas to GHS
                'currency' => $txData['currency'],
                'channel'  => $txData['channel'],
                'email'    => $txData['customer']['email'],
                'metadata' => $txData['metadata'] ?? [],
                'message'  => $txData['gateway_response'] ?? '',
                'data'     => $txData,
            ];
        } catch (\Throwable $e) {
            Log::error('Paystack verifyTransaction failed: ' . $e->getMessage());
            return ['status' => false, 'paid' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify a Paystack webhook signature.
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($expected, $signature);
    }

    /**
     * Generate a unique transaction reference.
     */
    public static function generateReference(string $prefix = 'TXN'): string
    {
        return strtoupper($prefix) . '-' . strtoupper(Str::random(8)) . '-' . time();
    }
}
