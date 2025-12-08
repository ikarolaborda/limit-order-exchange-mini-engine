<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Models\Trade;
use Illuminate\Support\Collection;

final class EloquentTradeRepository implements TradeRepositoryInterface
{
    public function findById(int $id): ?Trade
    {
        return Trade::find($id);
    }

    public function create(array $data): Trade
    {
        return Trade::create($data);
    }

    public function getTradesForSymbol(string $symbol, int $limit = 50): Collection
    {
        return Trade::query()
            ->where('symbol', $symbol)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getUserTrades(int $userId, int $limit = 50): Collection
    {
        return Trade::query()
            ->whereHas('buyOrder', fn ($q) => $q->where('user_id', $userId))
            ->orWhereHas('sellOrder', fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}

