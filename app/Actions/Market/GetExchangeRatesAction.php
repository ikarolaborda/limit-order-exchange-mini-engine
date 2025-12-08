<?php

declare(strict_types=1);

namespace App\Actions\Market;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

final class GetExchangeRatesAction
{
    use AsAction;

    private const CACHE_KEY = 'market:exchange_rates';
    private const CACHE_TTL = 120;
    private const COINGECKO_URL = 'https://api.coingecko.com/api/v3/simple/price';

    public function handle(): JsonResponse
    {
        $rates = Cache::tags(['market'])->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchFromCoinGecko();
        });

        return response()->json($rates);
    }

    private function fetchFromCoinGecko(): array
    {
        $fallback = [
            'BTC' => 95000,
            'ETH' => 3500,
            'source' => 'fallback',
            'cached_at' => now()->toIso8601String(),
        ];

        try {
            $response = Http::timeout(5)
                ->retry(2, 100)
                ->get(self::COINGECKO_URL, [
                    'ids' => 'bitcoin,ethereum',
                    'vs_currencies' => 'usd',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'BTC' => $data['bitcoin']['usd'] ?? $fallback['BTC'],
                    'ETH' => $data['ethereum']['usd'] ?? $fallback['ETH'],
                    'source' => 'coingecko',
                    'cached_at' => now()->toIso8601String(),
                ];
            }
        } catch (\Exception $e) {
            report($e);
        }

        return $fallback;
    }
}

