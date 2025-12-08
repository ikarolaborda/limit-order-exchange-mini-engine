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
        $users = User::all();

        if ($users->count() < 2) {
            return;
        }

        // Create sample trades between different user pairs
        // Trade 1: Alice (1) sells BTC to Bob (2)
        $this->createTrade(
            buyer: $users->get(1),
            seller: $users->get(0),
            symbol: 'BTC',
            price: '42500.00',
            amount: '0.15',
            fee: '95.625',
        );

        // Trade 2: Bob (2) sells BTC to Charlie (3)
        $this->createTrade(
            buyer: $users->get(2),
            seller: $users->get(1),
            symbol: 'BTC',
            price: '42750.00',
            amount: '0.08',
            fee: '51.30',
        );

        // Trade 3: Charlie (3) sells BTC to Diana (4)
        $this->createTrade(
            buyer: $users->get(3),
            seller: $users->get(2),
            symbol: 'BTC',
            price: '42600.00',
            amount: '0.25',
            fee: '159.75',
        );

        // Trade 4: Diana (4) sells ETH to Alice (1)
        $this->createTrade(
            buyer: $users->get(0),
            seller: $users->get(3),
            symbol: 'ETH',
            price: '2250.00',
            amount: '1.5',
            fee: '50.625',
        );

        // Trade 5: Alice (1) sells ETH to Bob (2)
        $this->createTrade(
            buyer: $users->get(1),
            seller: $users->get(0),
            symbol: 'ETH',
            price: '2275.00',
            amount: '2.0',
            fee: '68.25',
        );

        // Trade 6: Bob (2) sells ETH to Charlie (3)
        $this->createTrade(
            buyer: $users->get(2),
            seller: $users->get(1),
            symbol: 'ETH',
            price: '2280.00',
            amount: '1.0',
            fee: '34.20',
        );

        // Trade 7: Charlie (3) sells ETH to Diana (4)
        $this->createTrade(
            buyer: $users->get(3),
            seller: $users->get(2),
            symbol: 'ETH',
            price: '2290.00',
            amount: '0.5',
            fee: '17.175',
        );

        // Trade 8: Diana (4) sells BTC to Alice (1)
        $this->createTrade(
            buyer: $users->get(0),
            seller: $users->get(3),
            symbol: 'BTC',
            price: '42800.00',
            amount: '0.10',
            fee: '64.20',
        );

        // Create some OPEN orders for the orderbook demonstration
        $this->createOpenOrders($users);
    }

    private function createOpenOrders(\Illuminate\Support\Collection $users): void
    {
        // BTC buy orders at various prices (Bob, Charlie)
        $this->createOpenOrder($users->get(1), 'BTC', 'buy', '89500.00', '0.05');
        $this->createOpenOrder($users->get(2), 'BTC', 'buy', '89000.00', '0.10');
        $this->createOpenOrder($users->get(3), 'BTC', 'buy', '88500.00', '0.08');

        // BTC sell orders at various prices (Charlie, Diana)
        $this->createOpenOrder($users->get(2), 'BTC', 'sell', '91000.00', '0.12');
        $this->createOpenOrder($users->get(3), 'BTC', 'sell', '91500.00', '0.06');
        $this->createOpenOrder($users->get(1), 'BTC', 'sell', '92000.00', '0.15');

        // ETH buy orders
        $this->createOpenOrder($users->get(0), 'ETH', 'buy', '3050.00', '2.0');
        $this->createOpenOrder($users->get(2), 'ETH', 'buy', '3000.00', '1.5');

        // ETH sell orders
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
