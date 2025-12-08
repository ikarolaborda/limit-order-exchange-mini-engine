<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Models\Asset;
use App\Models\User;
use App\Repositories\Eloquent\EloquentAssetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AssetRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAssetRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAssetRepository();
    }

    public function test_find_by_user_and_symbol(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
        ]);

        $found = $this->repository->findByUserAndSymbol($user->id, 'BTC');

        $this->assertNotNull($found);
        $this->assertEquals($asset->id, $found->id);
    }

    public function test_find_by_user_and_symbol_returns_null(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findByUserAndSymbol($user->id, 'BTC');

        $this->assertNull($found);
    }

    public function test_create_or_get_creates_new_asset(): void
    {
        $user = User::factory()->create();

        $asset = $this->repository->createOrGet($user->id, 'BTC');

        $this->assertDatabaseHas('assets', [
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'amount' => '0.00000000',
            'locked_amount' => '0.00000000',
        ]);
    }

    public function test_create_or_get_returns_existing_asset(): void
    {
        $user = User::factory()->create();
        $existingAsset = Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'amount' => '10',
        ]);

        $asset = $this->repository->createOrGet($user->id, 'BTC');

        $this->assertEquals($existingAsset->id, $asset->id);
    }

    public function test_lock_amount(): void
    {
        $user = User::factory()->create();
        Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'amount' => '10',
            'locked_amount' => '0',
        ]);

        $asset = $this->repository->lockAmount($user->id, 'BTC', '2');

        $this->assertEquals('8.00000000', $asset->amount);
        $this->assertEquals('2.00000000', $asset->locked_amount);
    }

    public function test_unlock_amount(): void
    {
        $user = User::factory()->create();
        Asset::factory()->create([
            'user_id' => $user->id,
            'symbol' => 'BTC',
            'amount' => '8',
            'locked_amount' => '2',
        ]);

        $asset = $this->repository->unlockAmount($user->id, 'BTC', '2');

        $this->assertEquals('10.00000000', $asset->amount);
        $this->assertEquals('0.00000000', $asset->locked_amount);
    }

    public function test_transfer_amount(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Asset::factory()->create([
            'user_id' => $sender->id,
            'symbol' => 'BTC',
            'amount' => '5',
            'locked_amount' => '5',
        ]);

        $this->repository->transferAmount($sender->id, $receiver->id, 'BTC', '3');

        $this->assertDatabaseHas('assets', [
            'user_id' => $sender->id,
            'symbol' => 'BTC',
            'locked_amount' => '2.00000000',
        ]);

        $this->assertDatabaseHas('assets', [
            'user_id' => $receiver->id,
            'symbol' => 'BTC',
            'amount' => '3.00000000',
        ]);
    }
}

