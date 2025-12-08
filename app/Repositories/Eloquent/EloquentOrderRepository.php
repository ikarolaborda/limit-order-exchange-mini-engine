<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Support\Collection;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findByIdWithLock(int $id): ?Order
    {
        return Order::whereKey($id)->lockForUpdate()->first();
    }

    public function getOpenOrdersForSymbol(string $symbol): Collection
    {
        return Order::query()
            ->where('symbol', $symbol)
            ->open()
            ->orderBy('created_at')
            ->get();
    }

    public function getUserOrders(int $userId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function updateStatus(Order $order, int $status): Order
    {
        $order->status = $status;

        if ($status !== Order::STATUS_OPEN) {
            $order->locked_usd = 0;
        }

        $order->save();

        return $order;
    }

    public function findMatchingCounterOrder(Order $order): ?Order
    {
        return Order::query()
            ->where('symbol', $order->symbol)
            ->where('status', Order::STATUS_OPEN)
            ->where('side', $order->side === 'buy' ? 'sell' : 'buy')
            ->where('amount', $order->amount)
            ->when(
                $order->side === 'buy',
                fn ($q) => $q->where('price', '<=', $order->price),
                fn ($q) => $q->where('price', '>=', $order->price)
            )
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }
}

