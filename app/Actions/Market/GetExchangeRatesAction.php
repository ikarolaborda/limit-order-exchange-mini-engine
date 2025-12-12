<?php

declare(strict_types=1);

namespace App\Actions\Market;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/exchange-rates',
    operationId: 'getExchangeRates',
    description: 'Retrieve current USD exchange rates for supported cryptocurrencies. Rates are fetched from CoinGecko and cached for 2 minutes. Falls back to predefined rates if the external API is unavailable.',
    summary: 'Get exchange rates',
    tags: ['Market'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Exchange rates retrieved successfully',
            content: new OA\JsonContent(ref: '#/components/schemas/ExchangeRates')
        ),
    ]
)]
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
