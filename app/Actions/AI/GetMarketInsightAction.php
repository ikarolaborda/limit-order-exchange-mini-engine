<?php

declare(strict_types=1);

namespace App\Actions\AI;

use App\Services\AI\AIInsightsServiceInterface;
use App\Services\AI\DTO\MarketInsightDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

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
