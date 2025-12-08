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
use Symfony\Component\HttpFoundation\Response;

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
