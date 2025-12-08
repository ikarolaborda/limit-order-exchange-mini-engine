<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Events\OrderMatched;
use App\Models\Asset;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_order_matches_existing_sell_order(): void
    {
        Event::fake([OrderMatched::class]);

        $seller = User::factory()->create(['balance' => 0]);
        Asset::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 1,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($seller);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $buyer = User::factory()->create(['balance' => 100000]);
        Asset::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'amount' => 0,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($buyer);
        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('trades', [
            'symbol' => 'BTC',
            'amount' => '0.5',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $seller->id,
            'status' => Order::STATUS_FILLED,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $buyer->id,
            'status' => Order::STATUS_FILLED,
        ]);

        Event::assertDispatched(OrderMatched::class);

        $seller->refresh();
        $buyer->refresh();

        $notional = 50000 * 0.5;
        $this->assertEquals($notional, $seller->balance);

        $buyerAsset = Asset::where('user_id', $buyer->id)
            ->where('symbol', 'BTC')
            ->first();
        $this->assertEquals('0.50000000', $buyerAsset->amount);
    }

    public function test_sell_order_matches_existing_buy_order(): void
    {
        Event::fake([OrderMatched::class]);

        $buyer = User::factory()->create(['balance' => 100000]);
        Asset::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'amount' => 0,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($buyer);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $seller = User::factory()->create(['balance' => 0]);
        Asset::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($seller);
        $response = $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('trades', [
            'symbol' => 'BTC',
        ]);

        Event::assertDispatched(OrderMatched::class);
    }

    public function test_orders_match_at_maker_price(): void
    {
        Event::fake([OrderMatched::class]);

        $seller = User::factory()->create(['balance' => 0]);
        Asset::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($seller);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 48000,
            'amount' => 0.5,
        ]);

        $buyer = User::factory()->create(['balance' => 100000]);
        Asset::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'amount' => 0,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($buyer);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $this->assertDatabaseHas('trades', [
            'price' => '48000',
        ]);
    }

    public function test_no_match_when_prices_do_not_cross(): void
    {
        Event::fake([OrderMatched::class]);

        $seller = User::factory()->create(['balance' => 0]);
        Asset::factory()->create([
            'user_id' => $seller->id,
            'symbol' => 'BTC',
            'amount' => 10,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($seller);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'sell',
            'price' => 55000,
            'amount' => 0.5,
        ]);

        $buyer = User::factory()->create(['balance' => 100000]);
        Asset::factory()->create([
            'user_id' => $buyer->id,
            'symbol' => 'BTC',
            'amount' => 0,
            'locked_amount' => 0,
        ]);

        Sanctum::actingAs($buyer);
        $this->postJson('/api/orders', [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => 50000,
            'amount' => 0.5,
        ]);

        $this->assertDatabaseMissing('trades', [
            'symbol' => 'BTC',
        ]);

        Event::assertNotDispatched(OrderMatched::class);
    }
}

