<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Eloquent\EloquentOrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentOrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentOrderRepository();
    }

    public function test_find_by_id_returns_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $found = $this->repository->findById($order->id);

        $this->assertNotNull($found);
        $this->assertEquals($order->id, $found->id);
    }

    public function test_find_by_id_returns_null_for_non_existent(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    public function test_get_open_orders_for_symbol(): void
    {
        $user = User::factory()->create();

        Order::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'status' => Order::STATUS_OPEN,
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'status' => Order::STATUS_FILLED,
        ]);

        Order::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'ETH',
            'status' => Order::STATUS_OPEN,
        ]);

        $orders = $this->repository->getOpenOrdersForSymbol('BTC');

        $this->assertCount(1, $orders);
        $this->assertEquals('BTC', $orders->first()->symbol);
    }

    public function test_get_user_orders_returns_orders_sorted_by_date(): void
    {
        $user = User::factory()->create();

        $order1 = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDay(),
        ]);

        $order2 = Order::factory()->create([
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $orders = $this->repository->getUserOrders($user->id);

        $this->assertCount(2, $orders);
        $this->assertEquals($order2->id, $orders->first()->id);
    }

    public function test_create_order(): void
    {
        $user = User::factory()->create();

        $order = $this->repository->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '0.5',
            'locked_usd' => '25375',
            'status' => Order::STATUS_OPEN,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'symbol' => 'BTC',
        ]);
    }

    public function test_update_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_OPEN,
            'locked_usd' => '25000',
        ]);

        $result = $this->repository->updateStatus($order, Order::STATUS_FILLED);

        $this->assertEquals(Order::STATUS_FILLED, $result->status);
        $this->assertEquals(0, $result->locked_usd);
    }

    public function test_find_matching_counter_order_for_buy(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        Asset::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => '10',
            'locked_amount' => '1',
        ]);

        $sellOrder = Order::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => '50000',
            'amount' => '0.5',
            'status' => Order::STATUS_OPEN,
        ]);

        $buyOrder = Order::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '51000',
            'amount' => '0.5',
            'status' => Order::STATUS_OPEN,
        ]);

        $match = $this->repository->findMatchingCounterOrder($buyOrder);

        $this->assertNotNull($match);
        $this->assertEquals($sellOrder->id, $match->id);
    }

    public function test_find_matching_counter_order_returns_null_when_no_match(): void
    {
        $buyer = User::factory()->create();

        $buyOrder = Order::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '45000',
            'amount' => '0.5',
            'status' => Order::STATUS_OPEN,
        ]);

        $match = $this->repository->findMatchingCounterOrder($buyOrder);

        $this->assertNull($match);
    }
}

