<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AI\AIInsightsService;
use App\Services\AI\AIInsightsServiceInterface;
use Illuminate\Support\ServiceProvider;

final class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIInsightsServiceInterface::class, AIInsightsService::class);
    }

    public function boot(): void
    {
        //
    }
}
