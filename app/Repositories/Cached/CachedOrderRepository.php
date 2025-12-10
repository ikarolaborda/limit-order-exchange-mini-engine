<?php

declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;

final readonly class CachedOrderRepository implements OrderRepositoryInterface
{
    private const int CACHE_TTL = 60;

    private const string CACHE_PREFIX = 'orders:';

    public function __construct(
        private OrderRepositoryInterface $repository,
        private CacheRepository $cache,
    ) {}

    public function findById(int $id): ?Order
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."id:{$id}",
            self::CACHE_TTL,
            fn (): ?Order => $this->repository->findById($id)
        );
    }

    public function findByIdWithLock(int $id): ?Order
    {
        return $this->repository->findByIdWithLock($id);
    }

    public function getOpenOrdersForSymbol(string $symbol, ?string $side = null, ?int $status = null): Collection
    {
        // Only cache the default (no filters) case for simplicity
        if ($side === null && $status === null) {
            return $this->cache->remember(
                self::CACHE_PREFIX."orderbook:{$symbol}",
                self::CACHE_TTL,
                fn (): Collection => $this->repository->getOpenOrdersForSymbol($symbol)
            );
        }

        return $this->repository->getOpenOrdersForSymbol($symbol, $side, $status);
    }

    public function getUserOrders(int $userId): Collection
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."user:{$userId}",
            self::CACHE_TTL,
            fn (): Collection => $this->repository->getUserOrders($userId)
        );
    }

    public function create(array $data): Order
    {
        $order = $this->repository->create($data);
        $this->invalidateCache($order);

        return $order;
    }

    public function updateStatus(Order $order, int $status): Order
    {
        $result = $this->repository->updateStatus($order, $status);
        $this->invalidateCache($order);

        return $result;
    }

    public function findMatchingCounterOrder(Order $order): ?Order
    {
        return $this->repository->findMatchingCounterOrder($order);
    }

    private function invalidateCache(Order $order): void
    {
        $this->cache->forget(self::CACHE_PREFIX."id:{$order->id}");
        $this->cache->forget(self::CACHE_PREFIX."orderbook:{$order->symbol}");
        $this->cache->forget(self::CACHE_PREFIX."user:{$order->user_id}");
    }
}
