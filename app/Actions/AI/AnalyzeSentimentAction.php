<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\DTO\SentimentAnalysisDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/ai/sentiment',
    operationId: 'analyzeSentiment',
    description: 'Analyzes the sentiment of the provided text using on-device ML inference. Returns sentiment classification (positive/negative/neutral), confidence score, and detailed label breakdown.',
    summary: 'Analyze text sentiment',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['text'],
            properties: [
                new OA\Property(property: 'text', type: 'string', maxLength: 5000, example: 'Bitcoin shows strong momentum with increasing institutional adoption.'),
            ]
        )
    ),
    tags: ['AI'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Sentiment analysis completed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'sentiment_analysis'),
                            new OA\Property(
                                property: 'attributes',
                                properties: [
                                    new OA\Property(property: 'sentiment', type: 'string', enum: ['positive', 'negative', 'neutral'], example: 'positive'),
                                    new OA\Property(property: 'score', type: 'number', format: 'float', example: 0.92),
                                    new OA\Property(
                                        property: 'labels',
                                        type: 'array',
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: 'label', type: 'string'),
                                                new OA\Property(property: 'score', type: 'number'),
                                            ],
                                            type: 'object'
                                        )
                                    ),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
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
