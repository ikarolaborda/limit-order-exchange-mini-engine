<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class CancelOrderAction
{
    use AsAction;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AssetRepositoryInterface $assetRepository,
    ) {}

    public function handle(Order $order): Order
    {
        $this->checkStatus($order);

        return DB::transaction(function () use ($order): Order {
            $side = $order->side |> strtoupper(...);

            if ($side === 'BUY') {
                $this->refundLockedUsd($order);
            } else {
                $this->unlockAsset($order);
            }

            return $this->orderRepository->updateStatus($order, Order::STATUS_CANCELLED);
        });
    }

    private function checkStatus(Order $order): void
    {
        if ($order->status !== Order::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'order' => 'Only open orders can be cancelled.',
            ]);
        }
    }

    private function refundLockedUsd(Order $order): void
    {
        $this->userRepository->incrementBalanceWithLock($order->user_id, $order->locked_usd);
    }

    private function unlockAsset(Order $order): void
    {
        $this->assetRepository->unlockAmount($order->user_id, $order->symbol, $order->amount);
    }

    public function asController(CancelOrderRequest $request, Order $order): JsonResponse
    {
        $order = $this->handle($order);

        return OrderResource::make($order)->response();
    }
}
