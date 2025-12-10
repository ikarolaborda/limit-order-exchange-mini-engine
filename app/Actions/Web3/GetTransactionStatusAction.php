<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\BlockchainTransaction;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/web3/transactions/{transaction}',
    operationId: 'getTransactionStatus',
    description: 'Retrieves the current status of a blockchain transaction, including block number and confirmation count. Updates the stored transaction record with the latest status.',
    summary: 'Get transaction status',
    security: [['sanctum' => []]],
    tags: ['Web3'],
    parameters: [
        new OA\Parameter(
            name: 'transaction',
            description: 'Transaction ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Transaction status retrieved',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
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
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'failed']),
                                    new OA\Property(property: 'block_number', type: 'integer', nullable: true),
                                    new OA\Property(property: 'confirmations', type: 'integer', nullable: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
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
            response: Response::HTTP_NOT_FOUND,
            description: 'Transaction not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')
        ),
    ]
)]
final class GetTransactionStatusAction
{
    use AsAction;

    public function __construct(
        private readonly Web3ServiceInterface $web3Service,
    ) {}

    public function handle(BlockchainTransaction $transaction): BlockchainTransaction
    {
        $status = $this->web3Service->getTransactionStatus($transaction->tx_hash);

        $transaction->update([
            'status' => $status->status,
            'block_number' => $status->blockNumber,
            'confirmations' => $status->confirmations,
        ]);

        return $transaction->fresh();
    }

    public function asController(BlockchainTransaction $transaction): JsonResponse
    {
        $transaction = $this->handle($transaction);

        return response()->json([
            'data' => [
                'type' => 'transactions',
                'id' => (string) $transaction->id,
                'attributes' => [
                    'tx_hash' => $transaction->tx_hash,
                    'from_address' => $transaction->from_address,
                    'to_address' => $transaction->to_address,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'block_number' => $transaction->block_number,
                    'confirmations' => $transaction->confirmations,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'updated_at' => $transaction->updated_at->toIso8601String(),
                ],
            ],
        ]);
    }
}
