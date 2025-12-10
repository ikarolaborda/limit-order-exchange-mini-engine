<?php

declare(strict_types=1);

namespace App\Actions\Matching;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use App\Support\Decimal;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class LockFundsForBuyAction
{
    use AsAction;

    private const string FEE_MULTIPLIER = '1.015';

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param  array{symbol: string, side: string, price: string|float, amount: string|float}  $data
     */
    public function handle(User $user, array $data): Order
    {
        $user = $this->userRepository->findByIdWithLock($user->id);
        $price = Decimal::from($data['price']);
        $amount = Decimal::from($data['amount']);

        $notional = $price->mul($amount);
        $withFee = $notional->mul(self::FEE_MULTIPLIER);

        $userBalance = Decimal::from($user->balance);

        if ($userBalance->isLessThan($withFee)) {
            throw ValidationException::withMessages(['balance' => 'Insufficient USD balance.']);
        }

        $this->userRepository->decrementBalance($user->id, $withFee->toString());

        return $this->orderRepository->create([
            'user_id' => $user->id,
            'symbol' => strtoupper($data['symbol']),
            'side' => 'buy',
            'price' => $price->toString(),
            'amount' => $amount->toString(),
            'locked_usd' => $withFee->toString(),
            'status' => Order::STATUS_OPEN,
        ]);
    }
}
