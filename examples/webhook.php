<?php

/**
 * VerifiedSMS PHP SDK — Webhook Receiver Example
 *
 * Configure this URL in Dashboard → Settings → Webhook URL
 */

require_once __DIR__ . '/../src/autoload.php';

use VerifiedSMS\Client;

// Get webhook payload and signature
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_VERIFIEDSMS_SIGNATURE'] ?? '';

// Your webhook secret from Dashboard → Settings
$webhookSecret = 'YOUR_WEBHOOK_SECRET';

// Verify signature
if (!Client::verifyWebhook($payload, $signature, $webhookSecret)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// Parse delivery update
$data = json_decode($payload, true);

$eventId     = $data['event'] ?? '';
$messageId   = $data['message_id'] ?? '';
$status      = $data['status'] ?? '';
$destination = $data['destination'] ?? '';
$timestamp   = $data['timestamp'] ?? '';

// Log the delivery update
$logEntry = sprintf(
    "[%s] Event: %s | Message: %s | Status: %s | Dest: %s\n",
    $timestamp,
    $eventId,
    $messageId,
    $status,
    $destination
);

file_put_contents(__DIR__ . '/webhook.log', $logEntry, FILE_APPEND | LOCK_EX);

// Process based on status
switch ($status) {
    case 'delivered':
        // Mark as delivered in your database
        // updateDeliveryStatus($messageId, 'delivered');
        break;

    case 'failed':
        // Handle failed delivery
        // markMessageFailed($messageId);
        break;
}

// Respond with 200 OK
http_response_code(200);
echo 'OK';
