<?php

/**
 * VerifiedSMS PHP SDK — Check Delivery Status Example
 */

require_once __DIR__ . '/../src/autoload.php';

use VerifiedSMS\Client;

$client = new Client('YOUR_API_KEY');

// Check status of a sent message
$status = $client->status('a1b2c3d4-e5f6-7890-abcd-ef1234567890');

echo "Message ID:      {$status->messageId}\n";
echo "Destination:     {$status->destination}\n";
echo "Message:         {$status->message}\n";
echo "Status:          {$status->status}\n";
echo "Delivery Status: {$status->deliveryStatus}\n";
echo "Cost:            Rs. {$status->cost}\n";
echo "Created:         {$status->createdAt}\n";
echo "Accepted:        {$status->acceptedAt}\n";
echo "Delivered:       {$status->deliveredAt}\n\n";

if ($status->isDelivered()) {
    echo "Message delivered successfully!\n";
} elseif ($status->isFailed()) {
    echo "Message delivery failed.\n";
} else {
    echo "Message still in transit...\n";
}
