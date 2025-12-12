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
    description: 'Retrieve orders for a specific trading symbol. By default returns open orders only. Orders are sorted by price and then by creation time.',
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
        new OA\Parameter(
            name: 'side',
            description: 'Filter by order side',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['buy', 'sell'], example: 'buy')
        ),
        new OA\Parameter(
            name: 'status',
            description: 'Filter by order status (1=open, 2=filled, 3=cancelled)',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', enum: [1, 2, 3], example: 1)
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

    public function handle(string $symbol, ?string $side = null, ?int $status = null): Collection
    {
        return $this->orderRepository->getOpenOrdersForSymbol($symbol, $side, $status);
    }

    public function asController(GetOrderbookRequest $request): OrderCollection
    {
        $validated = $request->validated();
        $orders = $this->handle(
            $validated['symbol'],
            $validated['side'] ?? null,
            $validated['status'] ?? null
        );

        return new OrderCollection($orders);
    }
}
