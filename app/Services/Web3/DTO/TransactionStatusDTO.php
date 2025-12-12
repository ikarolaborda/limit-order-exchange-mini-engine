<?php

declare(strict_types=1);

namespace App\Services\Web3\DTO;

final readonly class TransactionStatusDTO
{
    public function __construct(
        public string $transactionHash,
        public string $status,
        public ?int $blockNumber = null,
        public ?int $confirmations = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transactionHash: $data['transaction_hash'],
            status: $data['status'],
            blockNumber: $data['block_number'] ?? null,
            confirmations: $data['confirmations'] ?? null,
        );
    }
}
