<?php

declare(strict_types=1);

namespace App\Services\AI\DTO;

final readonly class SentimentAnalysisDTO
{
    public function __construct(
        public string $text,
        public string $label,
        public float $score,
        public string $sentiment,
    ) {}

    public static function fromPipelineResult(string $text, array $result): self
    {
        $label = $result['label'] ?? 'UNKNOWN';
        $score = $result['score'] ?? 0.0;

        // Normalize sentiment to: positive, negative, or neutral
        $sentiment = match (strtoupper($label)) {
            'POSITIVE', 'POS', '5 STARS', '4 STARS' => 'positive',
            'NEGATIVE', 'NEG', '1 STAR', '2 STARS' => 'negative',
            default => 'neutral',
        };

        return new self(
            text: $text,
            label: $label,
            score: (float) $score,
            sentiment: $sentiment,
        );
    }

    public function isPositive(): bool
    {
        return $this->sentiment === 'positive';
    }

    public function isNegative(): bool
    {
        return $this->sentiment === 'negative';
    }

    public function isNeutral(): bool
    {
        return $this->sentiment === 'neutral';
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'label' => $this->label,
            'score' => $this->score,
            'sentiment' => $this->sentiment,
            'confidence' => round($this->score * 100, 2),
        ];
    }
}
