<?php

declare(strict_types=1);

namespace VerifiedSMS\Exceptions;

class VerifiedSMSException extends \Exception
{
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
