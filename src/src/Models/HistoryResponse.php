<?php

declare(strict_types=1);

namespace VerifiedSMS\Models;

class HistoryResponse
{
    public function __construct(
        public readonly array $history,
        public readonly array $pagination,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            history: $data['history'],
            pagination: $data['pagination'],
        );
    }

    public function getTotal(): int
    {
        return (int) $this->pagination['total'];
    }

    public function getPage(): int
    {
        return (int) $this->pagination['page'];
    }

    public function getTotalPages(): int
    {
        return (int) $this->pagination['total_pages'];
    }

    public function toArray(): array
    {
        return [
            'history'    => $this->history,
            'pagination' => $this->pagination,
        ];
    }
}
