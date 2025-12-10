<?php

declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Models\Asset;
use Illuminate\Cache\Repository as CacheRepository;

final readonly class CachedAssetRepository implements AssetRepositoryInterface
{
    private const int CACHE_TTL = 60;

    private const string CACHE_PREFIX = 'assets:';

    public function __construct(
        private AssetRepositoryInterface $repository,
        private CacheRepository $cache,
    ) {}

    public function findByUserAndSymbol(int $userId, string $symbol): ?Asset
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."user:{$userId}:symbol:{$symbol}",
            self::CACHE_TTL,
            fn (): ?Asset => $this->repository->findByUserAndSymbol($userId, $symbol)
        );
    }

    public function findByUserAndSymbolWithLock(int $userId, string $symbol): ?Asset
    {
        return $this->repository->findByUserAndSymbolWithLock($userId, $symbol);
    }

    public function createOrGet(int $userId, string $symbol): Asset
    {
        $asset = $this->repository->createOrGet($userId, $symbol);
        $this->invalidateCache($userId, $symbol);

        return $asset;
    }

    public function lockAmount(int $userId, string $symbol, string $amount): Asset
    {
        $asset = $this->repository->lockAmount($userId, $symbol, $amount);
        $this->invalidateCache($userId, $symbol);

        return $asset;
    }

    public function unlockAmount(int $userId, string $symbol, string $amount): Asset
    {
        $asset = $this->repository->unlockAmount($userId, $symbol, $amount);
        $this->invalidateCache($userId, $symbol);

        return $asset;
    }

    public function transferAmount(int $fromUserId, int $toUserId, string $symbol, string $amount): void
    {
        $this->repository->transferAmount($fromUserId, $toUserId, $symbol, $amount);
        $this->invalidateCache($fromUserId, $symbol);
        $this->invalidateCache($toUserId, $symbol);
    }

    private function invalidateCache(int $userId, string $symbol): void
    {
        $this->cache->forget(self::CACHE_PREFIX."user:{$userId}:symbol:{$symbol}");
    }
}
