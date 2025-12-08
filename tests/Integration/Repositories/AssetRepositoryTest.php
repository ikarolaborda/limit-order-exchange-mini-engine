<?php

declare(strict_types=1);

namespace Tests\Integration\Repositories;

use App\Models\Asset;
use App\Models\User;
use App\Repositories\Eloquent\EloquentAssetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_finds_by_user_and_symbol(): void
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

    #[Test]
    public function it_returns_null_when_asset_not_found(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findByUserAndSymbol($user->id, 'BTC');

        $this->assertNull($found);
    }

    #[Test]
    public function it_creates_new_asset_when_not_existing(): void
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

    #[Test]
    public function it_returns_existing_asset_when_already_exists(): void
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

    #[Test]
    public function it_locks_amount(): void
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

    #[Test]
    public function it_unlocks_amount(): void
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

    #[Test]
    public function it_transfers_amount_between_users(): void
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
