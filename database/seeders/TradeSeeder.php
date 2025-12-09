<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        if (Trade::exists()) {
            return;
        }

        $users = User::all();

        if ($users->count() < 2) {
            return;
        }

        $this->createTrade(
            buyer: $users->get(1),
            seller: $users->get(0),
            symbol: 'BTC',
            price: '42500.00',
            amount: '0.15',
            fee: '95.625',
        );

        $this->createTrade(
            buyer: $users->get(2),
            seller: $users->get(1),
            symbol: 'BTC',
            price: '42750.00',
            amount: '0.08',
            fee: '51.30',
        );

        $this->createTrade(
            buyer: $users->get(3),
            seller: $users->get(2),
            symbol: 'BTC',
            price: '42600.00',
            amount: '0.25',
            fee: '159.75',
        );

        $this->createTrade(
            buyer: $users->get(0),
            seller: $users->get(3),
            symbol: 'ETH',
            price: '2250.00',
            amount: '1.5',
            fee: '50.625',
        );

        $this->createTrade(
            buyer: $users->get(1),
            seller: $users->get(0),
            symbol: 'ETH',
            price: '2275.00',
            amount: '2.0',
            fee: '68.25',
        );

        $this->createTrade(
            buyer: $users->get(2),
            seller: $users->get(1),
            symbol: 'ETH',
            price: '2280.00',
            amount: '1.0',
            fee: '34.20',
        );

        $this->createTrade(
            buyer: $users->get(3),
            seller: $users->get(2),
            symbol: 'ETH',
            price: '2290.00',
            amount: '0.5',
            fee: '17.175',
        );

        $this->createTrade(
            buyer: $users->get(0),
            seller: $users->get(3),
            symbol: 'BTC',
            price: '42800.00',
            amount: '0.10',
            fee: '64.20',
        );

        $this->createOpenOrders($users);
    }

    private function createOpenOrders(\Illuminate\Support\Collection $users): void
    {
        if (Order::where('status', Order::STATUS_OPEN)->exists()) {
            return;
        }

        $this->createOpenOrder($users->get(1), 'BTC', 'buy', '89500.00', '0.05');
        $this->createOpenOrder($users->get(2), 'BTC', 'buy', '89000.00', '0.10');
        $this->createOpenOrder($users->get(3), 'BTC', 'buy', '88500.00', '0.08');

        $this->createOpenOrder($users->get(2), 'BTC', 'sell', '91000.00', '0.12');
        $this->createOpenOrder($users->get(3), 'BTC', 'sell', '91500.00', '0.06');
        $this->createOpenOrder($users->get(1), 'BTC', 'sell', '92000.00', '0.15');

        $this->createOpenOrder($users->get(0), 'ETH', 'buy', '3050.00', '2.0');
        $this->createOpenOrder($users->get(2), 'ETH', 'buy', '3000.00', '1.5');

        $this->createOpenOrder($users->get(1), 'ETH', 'sell', '3200.00', '3.0');
        $this->createOpenOrder($users->get(3), 'ETH', 'sell', '3250.00', '1.0');
    }

    private function createOpenOrder(
        User $user,
        string $symbol,
        string $side,
        string $price,
        string $amount,
    ): void {
        $lockedUsd = $side === 'buy'
            ? bcmul(bcmul($price, $amount, 8), '1.015', 8)
            : '0';

        Order::create([
            'user_id' => $user->id,
            'symbol' => $symbol,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'locked_usd' => $lockedUsd,
            'status' => Order::STATUS_OPEN,
        ]);
    }

    private function createTrade(
        User $buyer,
        User $seller,
        string $symbol,
        string $price,
        string $amount,
        string $fee,
    ): void {
        $lockedUsd = bcmul(bcmul($price, $amount, 8), '1.015', 8);

        $buyOrder = Order::create([
            'user_id' => $buyer->id,
            'symbol' => $symbol,
            'side' => 'buy',
            'price' => $price,
            'amount' => $amount,
            'locked_usd' => $lockedUsd,
            'status' => Order::STATUS_FILLED,
        ]);

        $sellOrder = Order::create([
            'user_id' => $seller->id,
            'symbol' => $symbol,
            'side' => 'sell',
            'price' => $price,
            'amount' => $amount,
            'locked_usd' => '0',
            'status' => Order::STATUS_FILLED,
        ]);

        Trade::create([
            'buy_order_id' => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'symbol' => $symbol,
            'price' => $price,
            'amount' => $amount,
            'fee' => $fee,
        ]);
    }
}
