<?php

/**
 * VerifiedSMS PHP SDK — Send SMS Example
 *
 * Usage: php send.php
 */

require_once __DIR__ . '/../src/autoload.php';

use VerifiedSMS\Client;
use VerifiedSMS\Exceptions\*;

// Initialize client
$client = new Client('YOUR_API_KEY');

try {
    // Send to a single number
    $result = $client->send('98XXXXXXXX', 'Hello from VerifiedSMS PHP SDK!');

    echo "Message ID: {$result->messageId}\n";
    echo "SMS Count:  {$result->smsCount}\n";
    echo "Cost:       Rs. {$result->cost}\n";
    echo "Balance:    Rs. {$result->newBalance}\n\n";

    // Send to multiple numbers (NTC + Ncell auto-detected)
    $result = $client->send('98XXXXXXXX,97798YYYYYYYY', 'Bulk SMS test');

    echo "Bulk sent: {$result->smsCount} segments, Rs. {$result->cost}\n\n";

    // Send Unicode (Nepali) message
    $result = $client->send('98XXXXXXXX', 'नमस्ते! तपाईंलाई स्वागत छ।', [
        'type' => 2,
    ]);

    echo "Unicode SMS: {$result->smsCount} segments\n\n";

    // Send sensitive message (OTP) — content not stored
    $result = $client->send('98XXXXXXXX', 'Your OTP is 456789', [
        'sensitive' => true,
    ]);

    echo "OTP sent: {$result->messageId}\n";

} catch (InsufficientBalanceError $e) {
    echo "ERROR: Insufficient balance — {$e->getMessage()}\n";
} catch (AuthenticationError $e) {
    echo "ERROR: Bad API key — {$e->getMessage()}\n";
} catch (GatewayError $e) {
    echo "ERROR: Gateway failed — {$e->getMessage()}\n";
    if ($e->getMessageId()) {
        echo "  Message ID for tracking: {$e->getMessageId()}\n";
    }
} catch (VerifiedSMSException $e) {
    echo "ERROR: {$e->getMessage()} (HTTP {$e->getStatusCode()})\n";
}
