<?php

declare(strict_types=1);

namespace VerifiedSMS;

// Auto-load exception classes
foreach (glob(__DIR__ . '/Exceptions/*.php') as $file) {
    require_once $file;
}

// Auto-load model classes
foreach (glob(__DIR__ . '/Models/*.php') as $file) {
    require_once $file;
}

require_once __DIR__ . '/Client.php';
