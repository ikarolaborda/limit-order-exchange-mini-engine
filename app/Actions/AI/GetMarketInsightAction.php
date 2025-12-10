<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\DTO\MarketInsightDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/ai/market-insight',
    operationId: 'getMarketInsight',
    description: 'Generates comprehensive market insights for a cryptocurrency based on news text. Combines sentiment analysis with text classification to provide trading recommendations.',
    summary: 'Generate market insight',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['symbol', 'news_text'],
            properties: [
                new OA\Property(property: 'symbol', type: 'string', enum: ['BTC', 'ETH'], example: 'BTC'),
                new OA\Property(property: 'news_text', type: 'string', minLength: 20, maxLength: 5000, example: 'Major institutional investors announce significant Bitcoin purchases, signaling growing mainstream adoption.'),
            ]
        )
    ),
    tags: ['AI'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Market insight generated',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'market_insight'),
                            new OA\Property(
                                property: 'attributes',
                                properties: [
                                    new OA\Property(property: 'symbol', type: 'string', example: 'BTC'),
                                    new OA\Property(
                                        property: 'sentiment',
                                        properties: [
                                            new OA\Property(property: 'sentiment', type: 'string'),
                                            new OA\Property(property: 'score', type: 'number'),
                                        ],
                                        type: 'object'
                                    ),
                                    new OA\Property(property: 'categories', type: 'array', items: new OA\Items(type: 'object')),
                                    new OA\Property(property: 'recommendation', type: 'string', enum: ['strong_bullish', 'bullish', 'neutral', 'bearish', 'strong_bearish']),
                                    new OA\Property(property: 'summary', type: 'string'),
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
final class GetMarketInsightAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    public function handle(string $symbol, string $newsText): MarketInsightDTO
    {
        return $this->aiService->generateMarketInsight($symbol, $newsText);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'symbol' => ['required', 'string', Rule::in(['BTC', 'ETH'])],
            'news_text' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $result = $this->handle(
            $request->input('symbol'),
            $request->input('news_text')
        );

        return response()->json([
            'data' => [
                'type' => 'market_insight',
                'attributes' => $result->toArray(),
            ],
        ]);
    }
}
