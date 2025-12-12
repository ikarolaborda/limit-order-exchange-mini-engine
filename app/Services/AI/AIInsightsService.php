<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\DTO\MarketInsightDTO;
use App\Services\AI\DTO\SentimentAnalysisDTO;
use Codewithkyrian\Transformers\Pipelines\Pipeline;
use Illuminate\Support\Facades\Cache;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

final class AIInsightsService implements AIInsightsServiceInterface
{
    private const CACHE_TTL = 3600; // 1 hour

    private ?Pipeline $sentimentPipeline = null;

    private ?Pipeline $zeroShotPipeline = null;

    private function getSentimentPipeline(): Pipeline
    {
        if ($this->sentimentPipeline === null) {
            $this->sentimentPipeline = pipeline('sentiment-analysis');
        }

        return $this->sentimentPipeline;
    }

    private function getZeroShotPipeline(): Pipeline
    {
        if ($this->zeroShotPipeline === null) {
            $this->zeroShotPipeline = pipeline(
                'zero-shot-classification',
                'Xenova/mobilebert-uncased-mnli'
            );
        }

        return $this->zeroShotPipeline;
    }

    public function analyzeSentiment(string $text): SentimentAnalysisDTO
    {
        $cacheKey = 'ai_sentiment:'.md5($text);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($text) {
            $result = $this->getSentimentPipeline()($text);

            return SentimentAnalysisDTO::fromPipelineResult($text, $result);
        });
    }

    public function analyzeSentimentBatch(array $texts): array
    {
        $results = [];

        foreach ($texts as $text) {
            $results[] = $this->analyzeSentiment($text);
        }

        return $results;
    }

    public function generateMarketInsight(string $symbol, string $newsText): MarketInsightDTO
    {
        $cacheKey = 'ai_market_insight:'.md5($symbol.$newsText);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($symbol, $newsText) {
            // Analyze sentiment
            $sentiment = $this->analyzeSentiment($newsText);

            // Classify into market-related categories
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
        });
    }

    public function classifyText(string $text, array $categories): array
    {
        $cacheKey = 'ai_classify:'.md5($text.implode(',', $categories));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($text, $categories) {
            $result = $this->getZeroShotPipeline()($text, $categories);

            // Format the result
            $classifications = [];
            if (isset($result['labels']) && isset($result['scores'])) {
                foreach ($result['labels'] as $index => $label) {
                    $classifications[] = [
                        'label' => $label,
                        'score' => $result['scores'][$index] ?? 0.0,
                    ];
                }
            }

            return $classifications;
        });
    }
}
