<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Asset;

interface AssetRepositoryInterface
{
    public function findByUserAndSymbol(int $userId, string $symbol): ?Asset;

    public function findByUserAndSymbolWithLock(int $userId, string $symbol): ?Asset;

    public function createOrGet(int $userId, string $symbol): Asset;

    public function lockAmount(int $userId, string $symbol, string $amount): Asset;

    public function unlockAmount(int $userId, string $symbol, string $amount): Asset;

    public function transferAmount(int $fromUserId, int $toUserId, string $symbol, string $amount): void;
}

