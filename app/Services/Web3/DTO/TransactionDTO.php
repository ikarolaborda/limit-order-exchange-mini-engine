<?php

declare(strict_types=1);

namespace App\Services\Web3\DTO;

final readonly class TransactionDTO
{
    public function __construct(
        public string $transactionHash,
        public string $from,
        public string $to,
        public string $amount,
        public string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            transactionHash: $data['transaction_hash'],
            from: $data['from'],
            to: $data['to'],
            amount: $data['amount'],
            status: $data['status'],
        );
    }
}
