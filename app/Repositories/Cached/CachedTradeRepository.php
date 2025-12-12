<?php

declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Models\Trade;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

final readonly class CachedTradeRepository implements TradeRepositoryInterface
{
    private const int CACHE_TTL = 30;

    private const string CACHE_PREFIX = 'trades:';

    public function __construct(
        private TradeRepositoryInterface $repository,
        private CacheRepository $cache,
    ) {}

    public function findById(int $id): ?Trade
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."id:{$id}",
            self::CACHE_TTL,
            fn (): ?Trade => $this->repository->findById($id)
        );
    }

    public function create(array $data): Trade
    {
        $trade = $this->repository->create($data);
        $this->invalidateCache($trade);

        return $trade;
    }

    public function getTradesForSymbol(string $symbol, int $limit = 50): Collection
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."symbol:{$symbol}:limit:{$limit}",
            self::CACHE_TTL,
            fn (): Collection => $this->repository->getTradesForSymbol($symbol, $limit)
        );
    }

    public function getUserTrades(int $userId, int $limit = 50): Collection
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."user:{$userId}:limit:{$limit}",
            self::CACHE_TTL,
            fn (): Collection => $this->repository->getUserTrades($userId, $limit)
        );
    }

    private function invalidateCache(Trade $trade): void
    {
        $this->cache->forget(self::CACHE_PREFIX."id:{$trade->id}");
        $this->cache->forget(self::CACHE_PREFIX."symbol:{$trade->symbol}:limit:50");
    }
}
