<?php

declare(strict_types=1);

namespace App\Actions\Matching;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use App\Support\Decimal;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class LockAssetForSellAction
{
    use AsAction;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly AssetRepositoryInterface $assetRepository,
    ) {}

    /**
     * @param  array{symbol: string, side: string, price: string|float, amount: string|float}  $data
     */
    public function handle(User $user, array $data): Order
    {
        $symbol = strtoupper($data['symbol']);
        $price = Decimal::from($data['price']);
        $amount = Decimal::from($data['amount']);

        $asset = $this->assetRepository->findByUserAndSymbolWithLock($user->id, $symbol);

        if ($asset === null) {
            throw ValidationException::withMessages(['asset' => 'Asset not found.']);
        }

        $assetAmount = Decimal::from($asset->amount);

        if ($assetAmount->isLessThan($amount)) {
            throw ValidationException::withMessages(['asset' => 'Insufficient asset balance.']);
        }

        $this->assetRepository->lockAmount($user->id, $symbol, $amount->toString());

        return $this->orderRepository->create([
            'user_id' => $user->id,
            'symbol' => $symbol,
            'side' => 'sell',
            'price' => $price->toString(),
            'amount' => $amount->toString(),
            'locked_usd' => 0,
            'status' => Order::STATUS_OPEN,
        ]);
    }
}
