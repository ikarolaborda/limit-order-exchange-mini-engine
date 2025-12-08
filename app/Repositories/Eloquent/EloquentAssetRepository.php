<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Models\Asset;

final class EloquentAssetRepository implements AssetRepositoryInterface
{
    public function findByUserAndSymbol(int $userId, string $symbol): ?Asset
    {
        return Asset::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->first();
    }

    public function findByUserAndSymbolWithLock(int $userId, string $symbol): ?Asset
    {
        return Asset::where('user_id', $userId)
            ->where('symbol', $symbol)
            ->lockForUpdate()
            ->first();
    }

    public function createOrGet(int $userId, string $symbol): Asset
    {
        return Asset::firstOrCreate(
            ['user_id' => $userId, 'symbol' => $symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );
    }

    public function lockAmount(int $userId, string $symbol, string $amount): Asset
    {
        $asset = $this->findByUserAndSymbolWithLock($userId, $symbol);

        if ($asset === null) {
            throw new \RuntimeException('Asset not found');
        }

        $asset->decrement('amount', $amount);
        $asset->increment('locked_amount', $amount);

        return $asset->fresh();
    }

    public function unlockAmount(int $userId, string $symbol, string $amount): Asset
    {
        $asset = $this->findByUserAndSymbolWithLock($userId, $symbol);

        if ($asset === null) {
            throw new \RuntimeException('Asset not found');
        }

        $asset->increment('amount', $amount);
        $asset->decrement('locked_amount', $amount);

        return $asset->fresh();
    }

    public function transferAmount(int $fromUserId, int $toUserId, string $symbol, string $amount): void
    {
        $fromAsset = $this->findByUserAndSymbolWithLock($fromUserId, $symbol);
        $toAsset = $this->createOrGet($toUserId, $symbol);

        if ($fromAsset === null) {
            throw new \RuntimeException('Source asset not found');
        }

        $fromAsset->decrement('locked_amount', $amount);
        $toAsset->increment('amount', $amount);
    }
}

