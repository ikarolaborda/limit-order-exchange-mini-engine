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
    path: '/api/ai/sentiment/batch',
    operationId: 'analyzeSentimentBatch',
    description: 'Analyzes sentiment for multiple texts in a single request. Efficient for bulk analysis of market news or social media posts. Maximum 10 texts per request.',
    summary: 'Batch sentiment analysis',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['texts'],
            properties: [
                new OA\Property(
                    property: 'texts',
                    type: 'array',
                    items: new OA\Items(type: 'string', maxLength: 2000),
                    minItems: 1,
                    maxItems: 10,
                    example: ['Bitcoin hits new high', 'Market faces uncertainty amid regulations']
                ),
            ]
        )
    ),
    tags: ['AI'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Batch analysis completed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'sentiment_analysis'),
                                new OA\Property(
                                    property: 'attributes',
                                    properties: [
                                        new OA\Property(property: 'sentiment', type: 'string'),
                                        new OA\Property(property: 'score', type: 'number'),
                                        new OA\Property(property: 'labels', type: 'array', items: new OA\Items(type: 'object')),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        )
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
final class AnalyzeSentimentBatchAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    /**
     * @param  array<string>  $texts
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
                fn (SentimentAnalysisDTO $dto) => [
                    'type' => 'sentiment_analysis',
                    'attributes' => $dto->toArray(),
                ],
                $results
            ),
        ]);
    }
}
