<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['balance' => 100000]);
        Asset::factory()->create([
            'user_id' => $this->user->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);
    }

    public function test_can_create_buy_order(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'type',
                    'id',
                    'attributes' => [
                        'symbol',
                        'side',
                        'price',
                        'amount',
                        'status',
                        'locked_usd',
                    ],
                    'links',
                    'meta',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'status' => Order::STATUS_OPEN,
        ]);
    }

    public function test_can_create_sell_order(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $response->assertStatus(201);

        $asset = Asset::where('user_id', $this->user->id)
            ->where('symbol', 'BTC')
            ->first();

        $this->assertEquals('9.50000000', $asset->amount);
        $this->assertEquals('0.50000000', $asset->locked_amount);
    }

    public function test_cannot_create_buy_order_with_insufficient_balance(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['balance']);
    }

    public function test_cannot_create_sell_order_with_insufficient_asset(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 50000,
            'amount' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['asset']);
    }

    public function test_validates_symbol_allowlist(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', [
            'symbol' => 'DOGE',
            'side' => 'buy',
            'price' => 1,
            'amount' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symbol']);
    }

    public function test_can_get_orderbook(): void
    {
        Sanctum::actingAs($this->user);

        Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'symbol' => 'BTC',
            'status' => Order::STATUS_OPEN,
        ]);

        $response = $this->getJson('/api/orders?symbol=BTC');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes',
                    ],
                ],
                'meta',
            ]);
    }

    public function test_can_get_my_orders(): void
    {
        Sanctum::actingAs($this->user);

        Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/my-orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_cancel_own_order(): void
    {
        Sanctum::actingAs($this->user);

        $order = Order::factory()->create([
            'user_id' => $this->user->id,
            'side' => 'buy',
            'locked_usd' => 25000,
            'status' => Order::STATUS_OPEN,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_CANCELLED,
        ]);
    }

    public function test_cannot_cancel_other_users_order(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Order::STATUS_OPEN,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422);
    }
}

