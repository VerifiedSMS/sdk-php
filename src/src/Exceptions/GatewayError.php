<?php

declare(strict_types=1);

namespace VerifiedSMS\Exceptions;

class GatewayError extends VerifiedSMSException
{
    private ?string $messageId;

    public function __construct(string $message, int $statusCode = 502, ?string $messageId = null)
    {
        parent::__construct($message, $statusCode);
        $this->messageId = $messageId;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }
}
