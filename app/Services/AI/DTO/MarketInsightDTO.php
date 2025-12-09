<?php

declare(strict_types=1);

namespace App\Services\AI\DTO;

final readonly class MarketInsightDTO
{
    public function __construct(
        public string $symbol,
        public string $newsText,
        public SentimentAnalysisDTO $sentiment,
        public string $recommendation,
        public array $categories,
        public string $summary,
    ) {}

    public static function create(
        string $symbol,
        string $newsText,
        SentimentAnalysisDTO $sentiment,
        array $categories,
    ): self {
        // Generate recommendation based on sentiment
        $recommendation = match ($sentiment->sentiment) {
            'positive' => $sentiment->score >= 0.8 ? 'bullish' : 'slightly_bullish',
            'negative' => $sentiment->score >= 0.8 ? 'bearish' : 'slightly_bearish',
            default => 'neutral',
        };

        // Generate a brief summary
        $summary = self::generateSummary($symbol, $sentiment, $categories);

        return new self(
            symbol: $symbol,
            newsText: $newsText,
            sentiment: $sentiment,
            recommendation: $recommendation,
            categories: $categories,
            summary: $summary,
        );
    }

    private static function generateSummary(
        string $symbol,
        SentimentAnalysisDTO $sentiment,
        array $categories,
    ): string {
        $sentimentText = match ($sentiment->sentiment) {
            'positive' => 'positive',
            'negative' => 'negative',
            default => 'neutral',
        };

        $confidence = round($sentiment->score * 100);
        $topCategory = !empty($categories) ? $categories[0]['label'] ?? 'general' : 'general';

        return sprintf(
            'The news about %s shows %s sentiment (%d%% confidence) and relates to %s topics.',
            strtoupper($symbol),
            $sentimentText,
            $confidence,
            $topCategory
        );
    }

    public function toArray(): array
    {
        return [
            'symbol' => $this->symbol,
            'news_text' => $this->newsText,
            'sentiment' => $this->sentiment->toArray(),
            'recommendation' => $this->recommendation,
            'categories' => $this->categories,
            'summary' => $this->summary,
        ];
    }
}
