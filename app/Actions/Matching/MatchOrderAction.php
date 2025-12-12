<?php

declare(strict_types=1);

namespace App\Actions\Matching;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Events\OrderMatched;
use App\Models\Order;
use App\Models\Trade;
use App\Notifications\OrderFilledNotification;
use App\Support\Decimal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class MatchOrderAction
{
    use AsAction;

    private const string FEE_RATE = '0.015';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AssetRepositoryInterface $assetRepository,
        private readonly TradeRepositoryInterface $tradeRepository,
    ) {}

    public function handle(Order $order): ?Trade
    {
        if ($order->status !== Order::STATUS_OPEN) {
            return null;
        }

        return DB::transaction(function () use ($order): ?Trade {
            $order = $order->id
                |> $this->orderRepository->findByIdWithLock(...);

            if ($order === null || $order->status !== Order::STATUS_OPEN) {
                return null;
            }

            $counter = $order
                |> $this->orderRepository->findMatchingCounterOrder(...);

            if ($counter === null) {
                return null;
            }

            return $this->executeTrade($order, $counter);
        });
    }

    private function executeTrade(Order $order, Order $counter): Trade
    {
        [$buyerOrder, $sellerOrder] = $order->side === 'buy'
            ? [$order, $counter]
            : [$counter, $order];

        $tradePrice = $counter->price |> Decimal::from(...);
        $amount = $order->amount |> Decimal::from(...);

        $notional = $tradePrice->mul($amount);
        $fee = $notional->mul(self::FEE_RATE);
        $totalDebit = $notional->add($fee);

        $buyer = $buyerOrder->user_id |> $this->userRepository->findByIdWithLock(...);
        $seller = $sellerOrder->user_id |> $this->userRepository->findByIdWithLock(...);

        $buyerAsset = $this->assetRepository->createOrGet($buyer->id, $order->symbol);
        $sellerAsset = $this->assetRepository->findByUserAndSymbolWithLock($seller->id, $order->symbol);

        $lockedAmount = ($sellerAsset?->locked_amount ?? '0') |> Decimal::from(...);

        if ($sellerAsset === null || $lockedAmount->isLessThan($amount)) {
            throw ValidationException::withMessages(['order' => 'Insufficient locked asset.']);
        }

        $sellerAsset->decrement('locked_amount', $amount->toString());
        $buyerAsset->increment('amount', $amount->toString());

        $buyerLockedUsd = $buyerOrder->locked_usd |> Decimal::from(...);
        $refund = $buyerLockedUsd->sub($totalDebit);

        if ($refund->isNegative()) {
            throw ValidationException::withMessages(['order' => 'Locked funds insufficient.']);
        }

        if ($refund->isPositive()) {
            $buyer->increment('balance', $refund->toString());
        }

        $this->orderRepository->updateStatus($buyerOrder, Order::STATUS_FILLED);
        $this->orderRepository->updateStatus($sellerOrder, Order::STATUS_FILLED);

        $seller->increment('balance', $notional->toString());

        $trade = $this->tradeRepository->create([
            'buy_order_id' => $buyerOrder->id,
            'sell_order_id' => $sellerOrder->id,
            'symbol' => $order->symbol,
            'price' => $tradePrice->toString(),
            'amount' => $amount->toString(),
            'fee' => $fee->toString(),
        ]);

        DB::afterCommit(function () use ($trade, $buyer, $seller): void {
            event(new OrderMatched($trade));
            $buyer->notify(new OrderFilledNotification($trade, 'buy'));
            $seller->notify(new OrderFilledNotification($trade, 'sell'));
        });

        return $trade;
    }

    public function asController(Request $request): JsonResponse
    {
        $orderId = $request->integer('order_id');

        $order = $orderId
            ? $orderId |> $this->orderRepository->findById(...)
            : Order::open()->first();

        if ($order === null) {
            return response()->json(['trade' => null]);
        }

        $trade = $order |> $this->handle(...);

        return response()->json(['trade' => $trade]);
    }
}
