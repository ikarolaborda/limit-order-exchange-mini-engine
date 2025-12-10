<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByIdWithLock(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(array $data): User;

    public function incrementBalance(int $userId, string $amount): void;

    public function decrementBalance(int $userId, string $amount): void;

    public function incrementBalanceWithLock(int $userId, string $amount): void;

    public function decrementBalanceWithLock(int $userId, string $amount): void;
}
