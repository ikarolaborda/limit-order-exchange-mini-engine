<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Cached\CachedOrderRepository;
use App\Repositories\Eloquent\EloquentOrderRepository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CachedOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CachedOrderRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new CachedOrderRepository(
            new EloquentOrderRepository,
            app(CacheRepository::class)
        );
    }

    #[Test]
    public function it_caches_result_when_finding_by_id(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->repository->findById($order->id);

        $this->assertTrue(Cache::has("orders:id:{$order->id}"));
    }

    #[Test]
    public function it_invalidates_cache_on_create(): void
    {
        $user = User::factory()->create();

        $this->repository->getOpenOrdersForSymbol('BTC');
        $this->assertTrue(Cache::has('orders:orderbook:BTC'));

        $this->repository->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '0.5',
            'locked_usd' => '25375',
            'status' => Order::STATUS_OPEN,
        ]);

        $this->assertFalse(Cache::has('orders:orderbook:BTC'));
    }

    #[Test]
    public function it_invalidates_cache_on_status_update(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'status' => Order::STATUS_OPEN,
        ]);

        $this->repository->findById($order->id);
        $this->assertTrue(Cache::has("orders:id:{$order->id}"));

        $this->repository->updateStatus($order, Order::STATUS_FILLED);

        $this->assertFalse(Cache::has("orders:id:{$order->id}"));
    }

    #[Test]
    public function it_bypasses_cache_when_finding_with_lock(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->repository->findByIdWithLock($order->id);

        $this->assertFalse(Cache::has("orders:id:{$order->id}"));
    }
}
