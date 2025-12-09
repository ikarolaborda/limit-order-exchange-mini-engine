<?php

declare(strict_types=1);

namespace App\Services\Web3\DTO;

final readonly class WalletDTO
{
    public function __construct(
        public string $address,
        public string $message,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            address: $data['address'],
            message: $data['message'] ?? 'Wallet created successfully',
        );
    }
}
