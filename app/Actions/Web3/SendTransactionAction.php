<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Actions\Activity\LogActivityAction;
use App\Models\BlockchainTransaction;
use App\Models\User;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/web3/transactions',
    operationId: 'sendTransaction',
    description: 'Sends ETH from one of your wallets to another address. Requires the wallet password for signing. Transaction is recorded and can be tracked.',
    summary: 'Send ETH transaction',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['from', 'to', 'amount', 'password'],
            properties: [
                new OA\Property(property: 'from', type: 'string', minLength: 42, maxLength: 42, example: '0x742d35Cc6634C0532925a3b844Bc9e7595f...'),
                new OA\Property(property: 'to', type: 'string', minLength: 42, maxLength: 42, example: '0x8ba1f109551bD432803012645Hf136...'),
                new OA\Property(property: 'amount', type: 'string', example: '0.1'),
                new OA\Property(property: 'password', type: 'string', example: 'walletpassword'),
            ]
        )
    ),
    tags: ['Web3'],
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Transaction sent',
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
                                    new OA\Property(property: 'tx_hash', type: 'string', example: '0xabc123...'),
                                    new OA\Property(property: 'from_address', type: 'string'),
                                    new OA\Property(property: 'to_address', type: 'string'),
                                    new OA\Property(property: 'amount', type: 'string'),
                                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'confirmed', 'failed']),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
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
            description: 'Validation error or wallet not found',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
final class SendTransactionAction
{
    use AsAction;

    public function __construct(
        private readonly Web3ServiceInterface $web3Service,
    ) {}

    public function handle(
        User $user,
        string $fromAddress,
        string $toAddress,
        string $amount,
        string $password,
    ): BlockchainTransaction {
        $wallet = $user->wallets()->where('address', $fromAddress)->first();

        if (! $wallet) {
            throw ValidationException::withMessages([
                'from' => ['Wallet not found or does not belong to you.'],
            ]);
        }

        $result = $this->web3Service->sendTransaction($fromAddress, $toAddress, $amount, $password);

        return $user->blockchainTransactions()->create([
            'tx_hash' => $result->transactionHash,
            'from_address' => $result->from,
            'to_address' => $result->to,
            'amount' => $result->amount,
            'status' => $result->status,
        ]);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|string|size:42',
            'to' => 'required|string|size:42',
            'amount' => 'required|numeric|gt:0',
            'password' => 'required|string',
        ]);

        $transaction = $this->handle(
            $request->user(),
            $request->input('from'),
            $request->input('to'),
            $request->input('amount'),
            $request->input('password'),
        );

        LogActivityAction::run(
            $request->user(),
            sprintf(
                'Sent %s ETH from %s to %s',
                $transaction->amount,
                substr($transaction->from_address, 0, 10) . '...',
                substr($transaction->to_address, 0, 10) . '...'
            ),
            $request
        );

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
                    'created_at' => $transaction->created_at->toIso8601String(),
                ],
            ],
        ], 201);
    }
}
