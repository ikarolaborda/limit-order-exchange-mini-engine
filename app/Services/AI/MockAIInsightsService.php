<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\DTO\MarketInsightDTO;
use App\Services\AI\DTO\SentimentAnalysisDTO;

final class MockAIInsightsService implements AIInsightsServiceInterface
{
    private const POSITIVE_KEYWORDS = [
        'gain', 'surge', 'rally', 'adoption', 'partnership', 'launch', 'growth',
        'bullish', 'up', 'rise', 'strong', 'improve', 'success', 'positive',
    ];

    private const NEGATIVE_KEYWORDS = [
        'drop', 'fall', 'crash', 'concern', 'regulation', 'ban', 'hack', 'loss',
        'bearish', 'down', 'decline', 'weak', 'fail', 'negative', 'delay',
    ];

    public function analyzeSentiment(string $text): SentimentAnalysisDTO
    {
        $lowerText = strtolower($text);

        $positiveCount = $this->countKeywords($lowerText, self::POSITIVE_KEYWORDS);
        $negativeCount = $this->countKeywords($lowerText, self::NEGATIVE_KEYWORDS);

        if ($positiveCount > $negativeCount) {
            $score = min(0.95, 0.6 + ($positiveCount - $negativeCount) * 0.1);
            return new SentimentAnalysisDTO(
                text: $text,
                label: 'POSITIVE',
                score: $score,
                sentiment: 'positive',
            );
        }

        if ($negativeCount > $positiveCount) {
            $score = min(0.95, 0.6 + ($negativeCount - $positiveCount) * 0.1);
            return new SentimentAnalysisDTO(
                text: $text,
                label: 'NEGATIVE',
                score: $score,
                sentiment: 'negative',
            );
        }

        return new SentimentAnalysisDTO(
            text: $text,
            label: 'NEUTRAL',
            score: 0.5 + (crc32($text) % 30) / 100,
            sentiment: 'neutral',
        );
    }

    public function analyzeSentimentBatch(array $texts): array
    {
        return array_map(fn (string $text) => $this->analyzeSentiment($text), $texts);
    }

    public function generateMarketInsight(string $symbol, string $newsText): MarketInsightDTO
    {
        $sentiment = $this->analyzeSentiment($newsText);
        $categories = $this->classifyText($newsText, [
            'earnings',
            'regulation',
            'adoption',
            'technology',
            'partnership',
            'market_movement',
            'security',
            'competition',
        ]);

        return MarketInsightDTO::create(
            symbol: $symbol,
            newsText: $newsText,
            sentiment: $sentiment,
            categories: $categories,
        );
    }

    public function classifyText(string $text, array $categories): array
    {
        $lowerText = strtolower($text);
        $results = [];

        $categoryKeywords = [
            'earnings' => ['revenue', 'profit', 'earnings', 'quarterly', 'financial'],
            'regulation' => ['regulation', 'sec', 'law', 'compliance', 'government', 'oversight'],
            'adoption' => ['adoption', 'accept', 'use', 'integrate', 'mainstream'],
            'technology' => ['upgrade', 'update', 'development', 'tech', 'protocol', 'network'],
            'partnership' => ['partnership', 'partner', 'collaborate', 'alliance', 'deal'],
            'market_movement' => ['price', 'market', 'trade', 'volume', 'surge', 'drop'],
            'security' => ['hack', 'breach', 'security', 'vulnerability', 'attack'],
            'competition' => ['competitor', 'compete', 'rival', 'alternative'],
        ];

        foreach ($categories as $category) {
            $keywords = $categoryKeywords[$category] ?? [$category];
            $count = $this->countKeywords($lowerText, $keywords);
            $score = min(0.95, 0.1 + $count * 0.15);

            $results[] = [
                'label' => $category,
                'score' => $score,
            ];
        }

        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    private function countKeywords(string $text, array $keywords): int
    {
        $count = 0;
        foreach ($keywords as $keyword) {
            $count += substr_count($text, $keyword);
        }
        return $count;
    }
}
