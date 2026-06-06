<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class StatusResponse
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $destination,
        public readonly ?string $message,
        public readonly string $status,
        public readonly string $deliveryStatus,
        public readonly string $cost,
        public readonly string $createdAt,
        public readonly ?string $acceptedAt,
        public readonly ?string $deliveredAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            messageId: $data['message_id'],
            destination: $data['destination'],
            message: $data['message'] ?? null,
            status: $data['status'],
            deliveryStatus: $data['delivery_status'],
            cost: $data['cost'],
            createdAt: $data['created_at'],
            acceptedAt: $data['accepted_at'] ?? null,
            deliveredAt: $data['delivered_at'] ?? null,
        );
    }

    public function isDelivered(): bool
    {
        return $this->deliveryStatus === 'delivered';
    }

    public function isFailed(): bool
    {
        return $this->deliveryStatus === 'failed';
    }

    public function toArray(): array
    {
        return [
            'message_id'       => $this->messageId,
            'destination'      => $this->destination,
            'message'          => $this->message,
            'status'           => $this->status,
            'delivery_status'  => $this->deliveryStatus,
            'cost'             => $this->cost,
            'created_at'       => $this->createdAt,
            'accepted_at'      => $this->acceptedAt,
            'delivered_at'     => $this->deliveredAt,
        ];
    }
}
