<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Web3\Web3Service;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Support\ServiceProvider;

final class Web3ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Web3ServiceInterface::class, function () {
            return new Web3Service(
                baseUrl: config('services.web3.url'),
                apiKey: config('services.web3.api_key'),
            );
        });
    }
}
