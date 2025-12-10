<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function findByIdWithLock(int $id): ?Order;

    public function getOpenOrdersForSymbol(string $symbol, ?string $side = null, ?int $status = null): Collection;

    public function getUserOrders(int $userId): Collection;

    public function create(array $data): Order;

    public function updateStatus(Order $order, int $status): Order;

    public function findMatchingCounterOrder(Order $order): ?Order;
}
