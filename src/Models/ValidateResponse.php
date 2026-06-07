<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class ValidateResponse
{
    public function __construct(
        public readonly array $valid,
        public readonly array $invalid,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            valid: $data['valid'],
            invalid: $data['invalid'],
        );
    }

    public function hasInvalid(): bool
    {
        return !empty($this->invalid);
    }

    public function toArray(): array
    {
        return [
            'valid'   => $this->valid,
            'invalid' => $this->invalid,
        ];
    }
}
