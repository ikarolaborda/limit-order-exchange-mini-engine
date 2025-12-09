<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\DTO\SentimentAnalysisDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class AnalyzeSentimentAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    public function handle(string $text): SentimentAnalysisDTO
    {
        return $this->aiService->analyzeSentiment($text);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'max:5000'],
        ]);

        $result = $this->handle($request->input('text'));

        return response()->json([
            'data' => [
                'type' => 'sentiment_analysis',
                'attributes' => $result->toArray(),
            ],
        ]);
    }
}
