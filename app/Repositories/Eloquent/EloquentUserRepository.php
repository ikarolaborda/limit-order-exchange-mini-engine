<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByIdWithLock(int $id): ?User
    {
        return User::whereKey($id)->lockForUpdate()->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function incrementBalance(int $userId, string $amount): void
    {
        User::whereKey($userId)->increment('balance', $amount);
    }

    public function decrementBalance(int $userId, string $amount): void
    {
        User::whereKey($userId)->decrement('balance', $amount);
    }

    public function incrementBalanceWithLock(int $userId, string $amount): void
    {
        $user = $this->findByIdWithLock($userId);
        $user?->increment('balance', $amount);
    }

    public function decrementBalanceWithLock(int $userId, string $amount): void
    {
        $user = $this->findByIdWithLock($userId);
        $user?->decrement('balance', $amount);
    }
}

