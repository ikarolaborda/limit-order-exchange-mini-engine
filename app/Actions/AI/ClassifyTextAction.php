<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/ai/classify',
    operationId: 'classifyText',
    description: 'Classifies text into user-defined categories using zero-shot classification. Useful for categorizing market news, identifying trading signals, or sorting content by topic.',
    summary: 'Classify text into categories',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['text', 'categories'],
            properties: [
                new OA\Property(property: 'text', type: 'string', maxLength: 5000, example: 'The Fed announces rate decision next week'),
                new OA\Property(
                    property: 'categories',
                    type: 'array',
                    items: new OA\Items(type: 'string', maxLength: 50),
                    minItems: 2,
                    maxItems: 10,
                    example: ['monetary_policy', 'earnings', 'regulation', 'technical_analysis']
                ),
            ]
        )
    ),
    tags: ['AI'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Text classified successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'text_classification'),
                            new OA\Property(
                                property: 'attributes',
                                properties: [
                                    new OA\Property(property: 'text', type: 'string'),
                                    new OA\Property(
                                        property: 'classifications',
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
final class ClassifyTextAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    /**
     * @param  array<string>  $categories
     */
    public function handle(string $text, array $categories): array
    {
        return $this->aiService->classifyText($text, $categories);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'categories' => ['required', 'array', 'min:2', 'max:10'],
            'categories.*' => ['required', 'string', 'max:50'],
        ]);

        $result = $this->handle(
            $request->input('text'),
            $request->input('categories')
        );

        return response()->json([
            'data' => [
                'type' => 'text_classification',
                'attributes' => [
                    'text' => $request->input('text'),
                    'classifications' => $result,
                ],
            ],
        ]);
    }
}
