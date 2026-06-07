<?php

declare(strict_types=1);

namespace VerifiedSMS;

use VerifiedSMS\Exceptions\AuthenticationError;
use VerifiedSMS\Exceptions\GatewayError;
use VerifiedSMS\Exceptions\InsufficientBalanceError;
use VerifiedSMS\Exceptions\IPWhitelistError;
use VerifiedSMS\Exceptions\RateLimitError;
use VerifiedSMS\Exceptions\ServerError;
use VerifiedSMS\Exceptions\ValidationError;
use VerifiedSMS\Exceptions\VerifiedSMSException;
use VerifiedSMS\Models\BalanceResponse;
use VerifiedSMS\Models\HistoryResponse;
use VerifiedSMS\Models\SendResponse;
use VerifiedSMS\Models\StatusResponse;
use VerifiedSMS\Models\UsageResponse;
use VerifiedSMS\Models\ValidateKeyResponse;
use VerifiedSMS\Models\ValidateResponse;

class Client
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private ?resource $curlHandle = null;

    private const VERSION = '2.0.0';
    private const DEFAULT_BASE_URL = 'https://verifiedsms.com/api/v2';
    private const DEFAULT_TIMEOUT = 30;

    public function __construct(string $apiKey, array $options = [])
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($options['base_url'] ?? self::DEFAULT_BASE_URL, '/');
        $this->timeout = $options['timeout'] ?? self::DEFAULT_TIMEOUT;
    }

    public function __destruct()
    {
        if ($this->curlHandle !== null) {
            curl_close($this->curlHandle);
        }
    }

    /**
     * Send SMS to one or more Nepali mobile numbers.
     *
     * @param string $destination  Phone number(s), comma-separated
     * @param string $message      SMS content (1-1600 chars)
     * @param array{type?: int, sensitive?: bool} $options
     */
    public function send(string $destination, string $message, array $options = []): SendResponse
    {
        $params = [
            'key'         => $this->apiKey,
            'destination' => $destination,
            'message'     => $message,
        ];

        if (isset($options['type'])) {
            $params['type'] = (int) $options['type'];
        }

        if (!empty($options['sensitive'])) {
            $params['sensitive'] = 'true';
        }

        $response = $this->request('POST', '/send', $params);

        return new SendResponse($response['data']);
    }

    /**
     * Check delivery status of a sent message.
     */
    public function status(string $messageId): StatusResponse
    {
        $response = $this->request('GET', '/status', [
            'key'        => $this->apiKey,
            'message_id' => $messageId,
        ]);

        return new StatusResponse($response['data']);
    }

    /**
     * Check account balance.
     */
    public function balance(): BalanceResponse
    {
        $response = $this->request('GET', '/balance', [
            'key' => $this->apiKey,
        ]);

        return new BalanceResponse($response['data']);
    }

    /**
     * Get SMS history with optional filters.
     *
     * @param array{page?: int, limit?: int, date_from?: string, date_to?: string, status?: string, search?: string} $options
     */
    public function history(array $options = []): HistoryResponse
    {
        $params = array_merge(['key' => $this->apiKey], $options);
        $response = $this->request('GET', '/history', $params);

        return new HistoryResponse($response['data']);
    }

    /**
     * Validate Nepali phone numbers.
     */
    public function validate(string $destination): ValidateResponse
    {
        $response = $this->request('POST', '/validate', [
            'key'         => $this->apiKey,
            'destination' => $destination,
        ]);

        return new ValidateResponse($response['data']);
    }

    /**
     * Get daily or monthly usage statistics.
     */
    public function usage(string $period = 'daily'): UsageResponse
    {
        $response = $this->request('GET', '/usage', [
            'key'    => $this->apiKey,
            'period' => $period,
        ]);

        return new UsageResponse($response['data']);
    }

    /**
     * Validate API key.
     */
    public function validateKey(): ValidateKeyResponse
    {
        $response = $this->request('GET', '/validate_key', [
            'key' => $this->apiKey,
        ]);

        return new ValidateKeyResponse($response['data']);
    }

    /**
     * Verify a webhook signature.
     */
    public static function verifyWebhook(string $payload, string $signature, string $secret): bool
    {
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    /**
     * Calculate SMS count for a message.
     */
    public static function calculateSmsCount(string $message, int $type = 1): int
    {
        $charsPerSms = ($type === 2) ? 70 : 160;
        $length = mb_strlen($message);
        return (int) ceil($length / $charsPerSms);
    }

    /**
     * Normalize a Nepali phone number.
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-()]/', '', trim($phone));

        if (preg_match('/^00977(\d{10})$/', $phone, $m)) {
            return '977' . $m[1];
        }
        if (preg_match('/^\+977(\d{10})$/', $phone, $m)) {
            return '977' . $m[1];
        }
        if (preg_match('/^977(\d{10})$/', $phone)) {
            return $phone;
        }
        if (preg_match('/^(98|97)\d{8}$/', $phone)) {
            return '977' . $phone;
        }
        if (preg_match('/^(\d{10})$/', $phone, $m)) {
            return '977' . $m[1];
        }

        return $phone;
    }

    /**
     * Detect operator from phone number.
     */
    public static function detectOperator(string $phone): string
    {
        $normalized = self::normalizePhone($phone);
        $local = substr($normalized, -10);

        if (preg_match('/^98[0-3]/', $local)) {
            return 'ncell';
        }
        if (preg_match('/^98[4-8]/', $local)) {
            return 'ntc';
        }

        return 'unknown';
    }

    /**
     * Make an API request.
     *
     * @throws VerifiedSMSException
     */
    private function request(string $method, string $endpoint, array $params): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'User-Agent: VerifiedSMS-PHP/' . self::VERSION,
            ],
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        } else {
            $url .= '?' . http_build_query($params);
            $options[CURLOPT_URL] = $url;
        }

        curl_setopt_array($ch, $options);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new VerifiedSMSException("cURL error: {$error}");
        }

        $response = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VerifiedSMSException("Invalid JSON response: {$body}");
        }

        if ($response['status'] === 'success') {
            return $response;
        }

        $message = $response['message'] ?? 'Unknown error';
        $data = $response['data'] ?? [];

        switch ($httpCode) {
            case 400:
                throw new ValidationError($message, $httpCode);
            case 401:
                throw new AuthenticationError($message, $httpCode);
            case 402:
                throw new InsufficientBalanceError($message, $httpCode);
            case 403:
                throw new IPWhitelistError($message, $httpCode);
            case 429:
                throw new RateLimitError($message, $httpCode);
            case 500:
                throw new ServerError($message, $httpCode);
            case 502:
                throw new GatewayError($message, $httpCode, $data['message_id'] ?? null);
            default:
                throw new VerifiedSMSException($message, $httpCode);
        }
    }
}
