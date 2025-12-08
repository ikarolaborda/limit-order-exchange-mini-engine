<?php

declare(strict_types=1);

namespace App\Actions\Trade;

use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Http\Resources\TradeResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class GetTradesAction
{
    use AsAction;

    public function __construct(
        private readonly TradeRepositoryInterface $tradeRepository,
    ) {}

    public function handle(string $symbol, int $limit = 50): Collection
    {
        return $this->tradeRepository->getTradesForSymbol($symbol, $limit);
    }

    public function asController(): AnonymousResourceCollection
    {
        $symbol = request()->input('symbol', 'BTC');
        $trades = $this->handle($symbol);

        return TradeResource::collection($trades);
    }
}
