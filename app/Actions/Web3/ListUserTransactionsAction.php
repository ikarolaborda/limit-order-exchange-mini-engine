<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/web3/transactions',
    operationId: 'listTransactions',
    description: 'Returns all blockchain transactions for the authenticated user, sorted by most recent first.',
    summary: 'List user transactions',
    security: [['sanctum' => []]],
    tags: ['Web3'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of transactions',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'transactions'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                                new OA\Property(
                                    property: 'attributes',
                                    properties: [
                                        new OA\Property(property: 'tx_hash', type: 'string'),
                                        new OA\Property(property: 'from_address', type: 'string'),
                                        new OA\Property(property: 'to_address', type: 'string'),
                                        new OA\Property(property: 'amount', type: 'string'),
                                        new OA\Property(property: 'status', type: 'string'),
                                        new OA\Property(property: 'block_number', type: 'integer', nullable: true),
                                        new OA\Property(property: 'confirmations', type: 'integer', nullable: true),
                                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'meta',
                        properties: [
                            new OA\Property(property: 'total', type: 'integer', example: 5),
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
    ]
)]
final class ListUserTransactionsAction
{
    use AsAction;

    public function handle(User $user): Collection
    {
        return $user->blockchainTransactions()->orderByDesc('created_at')->get();
    }

    public function asController(Request $request): JsonResponse
    {
        $transactions = $this->handle($request->user());

        return response()->json([
            'data' => $transactions->map(fn ($tx) => [
                'type' => 'transactions',
                'id' => (string) $tx->id,
                'attributes' => [
                    'tx_hash' => $tx->tx_hash,
                    'from_address' => $tx->from_address,
                    'to_address' => $tx->to_address,
                    'amount' => $tx->amount,
                    'status' => $tx->status,
                    'block_number' => $tx->block_number,
                    'confirmations' => $tx->confirmations,
                    'created_at' => $tx->created_at->toIso8601String(),
                ],
            ]),
            'meta' => [
                'total' => $transactions->count(),
            ],
        ]);
    }
}
