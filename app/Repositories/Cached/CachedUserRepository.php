<?php

declare(strict_types=1);

namespace App\Repositories\Cached;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Cache\Repository as CacheRepository;

final readonly class CachedUserRepository implements UserRepositoryInterface
{
    private const int CACHE_TTL = 60;

    private const string CACHE_PREFIX = 'users:';

    public function __construct(
        private UserRepositoryInterface $repository,
        private CacheRepository $cache,
    ) {}

    public function findById(int $id): ?User
    {
        return $this->cache->remember(
            self::CACHE_PREFIX."id:{$id}",
            self::CACHE_TTL,
            fn (): ?User => $this->repository->findById($id)
        );
    }

    public function findByIdWithLock(int $id): ?User
    {
        return $this->repository->findByIdWithLock($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->cache->remember(
            self::CACHE_PREFIX.'email:'.md5($email),
            self::CACHE_TTL,
            fn (): ?User => $this->repository->findByEmail($email)
        );
    }

    public function create(array $data): User
    {
        $user = $this->repository->create($data);
        $this->invalidateCache($user);

        return $user;
    }

    public function incrementBalance(int $userId, string $amount): void
    {
        $this->repository->incrementBalance($userId, $amount);
        $this->cache->forget(self::CACHE_PREFIX."id:{$userId}");
    }

    public function decrementBalance(int $userId, string $amount): void
    {
        $this->repository->decrementBalance($userId, $amount);
        $this->cache->forget(self::CACHE_PREFIX."id:{$userId}");
    }

    public function incrementBalanceWithLock(int $userId, string $amount): void
    {
        $this->repository->incrementBalanceWithLock($userId, $amount);
        $this->cache->forget(self::CACHE_PREFIX."id:{$userId}");
    }

    public function decrementBalanceWithLock(int $userId, string $amount): void
    {
        $this->repository->decrementBalanceWithLock($userId, $amount);
        $this->cache->forget(self::CACHE_PREFIX."id:{$userId}");
    }

    private function invalidateCache(User $user): void
    {
        $this->cache->forget(self::CACHE_PREFIX."id:{$user->id}");
        $this->cache->forget(self::CACHE_PREFIX.'email:'.md5($user->email));
    }
}
