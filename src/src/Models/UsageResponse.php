<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class UsageResponse
{
    public function __construct(
        public readonly string $period,
        public readonly int $totalSms,
        public readonly string $totalCost,
        public readonly array $stats,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            period: $data['period'],
            totalSms: (int) $data['total_sms'],
            totalCost: $data['total_cost'],
            stats: $data['stats'],
        );
    }

    public function toArray(): array
    {
        return [
            'period'     => $this->period,
            'total_sms'  => $this->totalSms,
            'total_cost' => $this->totalCost,
            'stats'      => $this->stats,
        ];
    }
}
