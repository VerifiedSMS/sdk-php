<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class SendResponse
{
    public function __construct(
        public readonly string $messageId,
        public readonly int $smsCount,
        public readonly string $cost,
        public readonly string $newBalance,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            smsCount: (int) $data['sms_count'],
            cost: $data['cost'],
            newBalance: $data['new_balance'],
        );
    }

    public function toArray(): array
    {
        return [
            'message_id'  => $this->messageId,
            'sms_count'   => $this->smsCount,
            'cost'        => $this->cost,
            'new_balance' => $this->newBalance,
        ];
    }
}
