<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Requests\Order\GetOrderbookRequest;
use App\Http\Resources\OrderCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class GetOrderbookAction
{
    use AsAction;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function handle(string $symbol): Collection
    {
        return $this->orderRepository->getOpenOrdersForSymbol($symbol);
    }

    public function asController(GetOrderbookRequest $request): OrderCollection
    {
        $validated = $request->validated();
        $orders = $this->handle($validated['symbol']);

        return new OrderCollection($orders);
    }
}

