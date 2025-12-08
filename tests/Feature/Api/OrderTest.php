<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_creates_buy_order(): void
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

    #[Test]
    public function it_creates_sell_order(): void
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

    #[Test]
    public function it_rejects_buy_order_with_insufficient_balance(): void
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

    #[Test]
    public function it_rejects_sell_order_with_insufficient_asset(): void
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

    #[Test]
    public function it_validates_symbol_against_allowlist(): void
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

    #[Test]
    public function it_gets_orderbook(): void
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

    #[Test]
    public function it_gets_user_orders(): void
    {
        Sanctum::actingAs($this->user);

        Order::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/my-orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    #[Test]
    public function it_cancels_own_order(): void
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

    #[Test]
    public function it_rejects_cancelling_other_users_order(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $otherUser->id,
            'status' => Order::STATUS_OPEN,
        ]);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(403);
    }
}
