<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AI\AIInsightsService;
use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\MockAIInsightsService;
use Codewithkyrian\Transformers\Transformers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

final class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AIInsightsServiceInterface::class, function () {
            if (config('services.ai.use_mock', true)) {
                return new MockAIInsightsService();
            }

            return new AIInsightsService();
        });
    }

    public function boot(): void
    {
        if (! config('services.ai.use_mock', true)) {
            $cacheDir = storage_path('app/transformers');

            if (! is_dir($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            Transformers::setup()
                ->setCacheDir($cacheDir)
                ->setLogger(Log::getLogger())
                ->apply();
        }
    }
}
