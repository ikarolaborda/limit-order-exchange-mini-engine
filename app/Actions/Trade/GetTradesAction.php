<?php

declare(strict_types=1);

namespace App\Actions\Trade;

use App\Contracts\Repositories\TradeRepositoryInterface;
use App\Http\Resources\TradeResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/trades',
    operationId: 'getTrades',
    description: 'Retrieve recent trades for a specific trading symbol. Returns the most recent trades up to the specified limit.',
    summary: 'Get recent trades',
    tags: ['Trades'],
    parameters: [
        new OA\Parameter(
            name: 'symbol',
            description: 'Trading symbol to filter trades (defaults to BTC)',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', default: 'BTC', enum: ['BTC', 'ETH'], example: 'BTC')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Trades retrieved successfully',
            content: new OA\JsonContent(ref: '#/components/schemas/TradeCollection')
        ),
    ]
)]
final class GetTradesAction
{
    use AsAction;

    public function __construct(
        private readonly TradeRepositoryInterface $tradeRepository,
    ) {}

    public function handle(string $symbol, int $limit = 50): Collection
    {
        return $this->tradeRepository->getTradesForSymbol($symbol, $limit);
    }

    public function asController(): AnonymousResourceCollection
    {
        $symbol = request()->input('symbol', 'BTC');
        $trades = $this->handle($symbol);

        return TradeResource::collection($trades);
    }
}
