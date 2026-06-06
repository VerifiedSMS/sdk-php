<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class ValidateKeyResponse
{
    public function __construct(
        public readonly string $balance,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(balance: $data['balance']);
    }

    public function toArray(): array
    {
        return ['balance' => $this->balance];
    }
}
