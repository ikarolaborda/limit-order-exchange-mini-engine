<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Requests\Order\GetOrderbookRequest;
use App\Http\Resources\OrderCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/orders',
    operationId: 'getOrderbook',
    description: 'Retrieve all open orders for a specific trading symbol. Orders are sorted by price (ascending for sells, descending for buys) and then by creation time.',
    summary: 'Get orderbook',
    tags: ['Orders'],
    parameters: [
        new OA\Parameter(
            name: 'symbol',
            description: 'Trading symbol to filter orders',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'string', enum: ['BTC', 'ETH'], example: 'BTC')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Orderbook retrieved successfully',
            content: new OA\JsonContent(ref: '#/components/schemas/OrderCollection')
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Validation error - invalid symbol',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
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

