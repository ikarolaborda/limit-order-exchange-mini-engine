<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\DTO\SentimentAnalysisDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class AnalyzeSentimentBatchAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    /**
     * @param array<string> $texts
     * @return array<SentimentAnalysisDTO>
     */
    public function handle(array $texts): array
    {
        return $this->aiService->analyzeSentimentBatch($texts);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'texts' => ['required', 'array', 'min:1', 'max:10'],
            'texts.*' => ['required', 'string', 'max:2000'],
        ]);

        $results = $this->handle($request->input('texts'));

        return response()->json([
            'data' => array_map(
                fn(SentimentAnalysisDTO $dto) => [
                    'type' => 'sentiment_analysis',
                    'attributes' => $dto->toArray(),
                ],
                $results
            ),
        ]);
    }
}
