<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\Cached\CachedAssetRepository;
use App\Repositories\Cached\CachedOrderRepository;
use App\Repositories\Cached\CachedTradeRepository;
use App\Repositories\Cached\CachedUserRepository;
use App\Repositories\Eloquent\EloquentAssetRepository;
use App\Repositories\Eloquent\EloquentOrderRepository;
use App\Repositories\Eloquent\EloquentTradeRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            OrderRepositoryInterface::class,
            fn ($app): CachedOrderRepository => new CachedOrderRepository(
                new EloquentOrderRepository,
                $app->make(CacheRepository::class)
            )
        );

        $this->app->singleton(
            AssetRepositoryInterface::class,
            fn ($app): CachedAssetRepository => new CachedAssetRepository(
                new EloquentAssetRepository,
                $app->make(CacheRepository::class)
            )
        );

        $this->app->singleton(
            UserRepositoryInterface::class,
            fn ($app): CachedUserRepository => new CachedUserRepository(
                new EloquentUserRepository,
                $app->make(CacheRepository::class)
            )
        );

        $this->app->singleton(
            TradeRepositoryInterface::class,
            fn ($app): CachedTradeRepository => new CachedTradeRepository(
                new EloquentTradeRepository,
                $app->make(CacheRepository::class)
            )
        );
    }
}
