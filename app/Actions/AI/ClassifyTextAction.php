<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

final class ClassifyTextAction
{
    use AsAction;

    public function __construct(
        private readonly AIInsightsServiceInterface $aiService,
    ) {}

    /**
     * @param array<string> $categories
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
