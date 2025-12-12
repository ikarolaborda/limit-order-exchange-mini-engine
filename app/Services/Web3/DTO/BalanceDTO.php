<?php

declare(strict_types=1);

namespace App\Services\Web3\DTO;

final readonly class BalanceDTO
{
    public function __construct(
        public string $address,
        public string $balanceWei,
        public string $balanceEth,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            address: $data['address'],
            balanceWei: $data['balance_wei'],
            balanceEth: $data['balance_eth'],
        );
    }
}
