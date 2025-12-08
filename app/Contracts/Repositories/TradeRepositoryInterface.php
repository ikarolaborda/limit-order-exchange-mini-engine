<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Trade;
use Illuminate\Support\Collection;

interface TradeRepositoryInterface
{
    public function findById(int $id): ?Trade;

    public function create(array $data): Trade;

    public function getTradesForSymbol(string $symbol, int $limit = 50): Collection;

    public function getUserTrades(int $userId, int $limit = 50): Collection;
}

