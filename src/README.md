# VerifiedSMS PHP SDK

## Installation

### Composer

```bash
composer require verifiedsms/verifiedsms-php
```

### Manual

Copy `src/VerifiedSMS.php` into your project and require it:

```php
require_once 'path/to/VerifiedSMS.php';
```

## Quick Start

```php
use VerifiedSMS\Client;

$client = new Client('YOUR_API_KEY');

// Send SMS
$result = $client->send('98XXXXXXXX', 'Your OTP is 123456');
echo $result->message_id; // UUID

// Check status
$status = $client->status($result->message_id);
echo $status->delivery_status; // pending, accepted, delivered, failed

// Check balance
$balance = $client->balance();
echo $balance->balance; // "100.00"
```

## Configuration

```php
$client = new Client('YOUR_API_KEY');

// Optional: custom base URL (for testing)
$client = new Client('YOUR_API_KEY', [
    'base_url' => 'https://your-testing-server.com/api/v2',
    'timeout'  => 30,
]);
```

## Methods

### `send($destination, $message, $options = [])`

Send SMS to one or more numbers.

```php
$result = $client->send('98XXXXXXXX,97798YYYYYYYY', 'Hello!', [
    'type'      => 1,        // 1=Normal, 2=Unicode (default: 1)
    'sensitive' => true,     // Don't store message content (default: false)
]);

// Response: SendResponse
//   ->message_id   (string)  UUID
//   ->sms_count    (int)     Number of segments
//   ->cost         (string)  Cost in Rs.
//   ->new_balance  (string)  Remaining balance
```

### `status($messageId)`

Check delivery status.

```php
$status = $client->status('a1b2c3d4-e5f6-7890-abcd-ef1234567890');

// Response: StatusResponse
//   ->message_id       (string)
//   ->destination      (string)
//   ->message          (string|null)  null if sensitive
//   ->status           (string)  sent, failed, pending
//   ->delivery_status  (string)  pending, accepted, delivered, failed
//   ->cost             (string)
//   ->created_at       (string)
//   ->accepted_at      (string|null)
//   ->delivered_at     (string|null)
```

### `balance()`

Check account balance.

```php
$balance = $client->balance();

// Response: BalanceResponse
//   ->balance  (string)  e.g. "100.00"
```

### `history($options = [])`

Get SMS history with filters.

```php
$history = $client->history([
    'page'      => 1,
    'limit'     => 25,
    'date_from' => '2026-06-01',
    'date_to'   => '2026-06-30',
    'status'    => 'sent',
    'search'    => '98',
]);

// Response: HistoryResponse
//   ->history     (array)  of HistoryItem
//   ->pagination  (array)  total, page, per_page, total_pages
```

### `validate($destination)`

Validate phone numbers.

```php
$result = $client->validate('98XXXXXXXX,12345,abc');

// Response: ValidateResponse
//   ->valid    (array)  ["97798XXXXXXXX"]
//   ->invalid  (array)  ["12345", "abc"]
```

### `usage($period = 'daily')`

Get usage statistics.

```php
$usage = $client->usage('monthly');

// Response: UsageResponse
//   ->period      (string)
//   ->total_sms   (int)
//   ->total_cost  (string)
//   ->stats       (array)  of {date/month, sms_count, total_cost}
```

### `validateKey()`

Check if API key is valid.

```php
$result = $client->validateKey();

// Response: ValidateKeyResponse
//   ->balance  (string)
```

## Error Handling

```php
use VerifiedSMS\Client;
use VerifiedSMS\Exceptions\*;

try {
    $result = $client->send('98XXXXXXXX', 'Hello');
} catch (InsufficientBalanceError $e) {
    echo "Need more credit: {$e->getMessage()}";
} catch (AuthenticationError $e) {
    echo "Bad API key: {$e->getMessage()}";
} catch (IPWhitelistError $e) {
    echo "IP not allowed: {$e->getMessage()}";
} catch (RateLimitError $e) {
    echo "Slow down: {$e->getMessage()}";
} catch (GatewayError $e) {
    echo "Gateway failed: {$e->getMessage()}";
} catch (VerifiedSMSException $e) {
    echo "API error: {$e->getMessage()}";
}
```

## Webhook Verification

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_VERIFIEDSMS_SIGNATURE'] ?? '';

if (Client::verifyWebhook($payload, $signature, 'YOUR_WEBHOOK_SECRET')) {
    $data = json_decode($payload, true);
    // Process delivery update
    http_response_code(200);
    echo 'OK';
} else {
    http_response_code(401);
    echo 'Invalid signature';
}
```

## Sensitive Mode

For OTPs and confidential messages:

```php
$result = $client->send('98XXXXXXXX', 'Your OTP is 123456', [
    'sensitive' => true,
]);

// Message content is NOT stored on the server.
// You MUST store message_id yourself to check status later.
```

## PHP 8.0+ Features

If using PHP 8.0+, you can use named arguments:

```php
$result = $client->send(
    destination: '98XXXXXXXX',
    message: 'Hello',
    options: ['type' => 2, 'sensitive' => true]
);
```
