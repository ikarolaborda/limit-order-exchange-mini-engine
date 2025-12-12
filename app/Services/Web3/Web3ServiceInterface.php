<?php

declare(strict_types=1);

namespace App\Services\Web3;

use App\Services\Web3\DTO\BalanceDTO;
use App\Services\Web3\DTO\TransactionDTO;
use App\Services\Web3\DTO\TransactionStatusDTO;
use App\Services\Web3\DTO\WalletDTO;

interface Web3ServiceInterface
{
    public function createWallet(string $password): WalletDTO;

    public function getBalance(string $address): BalanceDTO;

    public function listWallets(): array;

    public function sendTransaction(string $from, string $to, string $amount, string $password): TransactionDTO;

    public function getTransactionStatus(string $txHash): TransactionStatusDTO;

    public function isHealthy(): bool;
}
