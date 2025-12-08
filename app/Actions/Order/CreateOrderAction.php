<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Actions\Matching\LockAssetForSellAction;
use App\Actions\Matching\LockFundsForBuyAction;
use App\Actions\Matching\MatchOrderAction;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/orders',
    operationId: 'createOrder',
    description: 'Place a new limit order. For buy orders, USD funds are locked (including 1.5% fee). For sell orders, the asset amount is locked. If a matching order exists at the same or better price, a trade is executed immediately.',
    summary: 'Create a new order',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CreateOrderRequest')
    ),
    tags: ['Orders'],
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Order created successfully',
            content: new OA\JsonContent(ref: '#/components/schemas/CreateOrderResponse')
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Validation error or insufficient funds/assets',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
final class CreateOrderAction
{
    use AsAction;

    public function __construct(
        private readonly LockFundsForBuyAction $lockFundsForBuy,
        private readonly LockAssetForSellAction $lockAssetForSell,
        private readonly MatchOrderAction $matchOrder,
    ) {}

    /**
     * @param array{symbol: string, side: string, price: string|float, amount: string|float} $data
     * @return array{order: Order, trade: ?Trade}
     */
    public function handle(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            $order = $data['side']
                |> strtoupper(...)
                |> (fn (string $side): Order => $side === 'BUY'
                    ? $this->lockFundsForBuy->handle($user, $data)
                    : $this->lockAssetForSell->handle($user, $data));

            $trade = $order |> $this->matchOrder->handle(...);

            return [
                'order' => $order->fresh(),
                'trade' => $trade,
            ];
        });
    }

    public function asController(CreateOrderRequest $request): JsonResponse
    {
        $result = $this->handle($request->user(), $request->validated());

        return OrderResource::make($result['order'])
            ->additional(['trade' => $result['trade']])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
