<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

final class GetMarketSentimentAction
{
    use AsAction;

    private const CACHE_TTL = 3600;

    private const MARKET_SCENARIOS = [
        'BTC' => [
            'positive' => 'Bitcoin continues to gain institutional adoption as major banks announce cryptocurrency custody services. Trading volumes are up 30% this week.',
            'negative' => 'Bitcoin faces selling pressure amid regulatory concerns. Several countries announce stricter cryptocurrency oversight.',
            'neutral' => 'Bitcoin trades in a narrow range as markets await key economic data. Institutional interest remains steady.',
        ],
        'ETH' => [
            'positive' => 'Ethereum network activity surges as new DeFi protocols launch. Gas fees remain low after recent optimizations.',
            'negative' => 'Ethereum developers delay major upgrade citing technical issues. Network congestion increases.',
            'neutral' => 'Ethereum maintains stable price action as developers prepare for upcoming protocol improvements.',
        ],
    ];

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    public function handle(): array
    {
        return Cache::remember('market_sentiment_overview', self::CACHE_TTL, function () {
            $sentiments = [];

            foreach (['BTC', 'ETH'] as $symbol) {
                $scenario = $this->selectScenario($symbol);
                $newsText = self::MARKET_SCENARIOS[$symbol][$scenario];

                $insight = $this->aiService->generateMarketInsight($symbol, $newsText);

                $sentiments[$symbol] = [
                    'symbol' => $symbol,
                    'sentiment' => $insight->sentiment->sentiment,
                    'confidence' => $insight->sentiment->score,
                    'recommendation' => $insight->recommendation,
                    'summary' => $insight->summary,
                    'top_category' => $insight->categories[0]['label'] ?? 'general',
                ];
            }

            return $sentiments;
        });
    }

    private function selectScenario(string $symbol): string
    {
        $hash = crc32($symbol . date('Y-m-d-H'));
        $options = ['positive', 'negative', 'neutral'];

        return $options[$hash % 3];
    }

    public function asController(): JsonResponse
    {
        $sentiments = $this->handle();

        return response()->json([
            'data' => [
                'type' => 'market_sentiment',
                'attributes' => $sentiments,
            ],
        ]);
    }
}
