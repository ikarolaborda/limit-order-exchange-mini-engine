<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\DTO\SentimentAnalysisDTO;
use App\Services\AI\DTO\MarketInsightDTO;

interface AIInsightsServiceInterface
{
    /**
     * Analyze sentiment of a single text.
     */
    public function analyzeSentiment(string $text): SentimentAnalysisDTO;

    /**
     * Analyze sentiment of multiple texts in batch.
     *
     * @param array<string> $texts
     * @return array<SentimentAnalysisDTO>
     */
    public function analyzeSentimentBatch(array $texts): array;

    /**
     * Generate market insight from news/text about a trading symbol.
     */
    public function generateMarketInsight(string $symbol, string $newsText): MarketInsightDTO;

    /**
     * Classify text into predefined categories.
     *
     * @param array<string> $categories
     */
    public function classifyText(string $text, array $categories): array;
}
