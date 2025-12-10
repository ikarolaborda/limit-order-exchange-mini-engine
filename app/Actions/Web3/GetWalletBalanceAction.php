<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\UserWallet;
use App\Services\Web3\DTO\BalanceDTO;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/web3/wallets/{wallet}/balance',
    operationId: 'getWalletBalance',
    description: 'Retrieves the current ETH balance for a specific wallet. Returns both Wei and ETH denominations.',
    summary: 'Get wallet balance',
    security: [['sanctum' => []]],
    tags: ['Web3'],
    parameters: [
        new OA\Parameter(
            name: 'wallet',
            description: 'Wallet ID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Balance retrieved',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'balances'),
                            new OA\Property(property: 'id', type: 'string', example: '0x742d35Cc6634C0532925a3b844Bc9e7595f...'),
                            new OA\Property(
                                property: 'attributes',
                                properties: [
                                    new OA\Property(property: 'address', type: 'string'),
                                    new OA\Property(property: 'balance_wei', type: 'string', example: '1000000000000000000'),
                                    new OA\Property(property: 'balance_eth', type: 'string', example: '1.0'),
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
            description: 'Wallet not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')
        ),
    ]
)]
final class GetWalletBalanceAction
{
    use AsAction;

    public function __construct(
        private readonly Web3ServiceInterface $web3Service,
    ) {}

    public function handle(string $address): BalanceDTO
    {
        return $this->web3Service->getBalance($address);
    }

    public function asController(UserWallet $wallet): JsonResponse
    {
        $balance = $this->handle($wallet->address);

        return response()->json([
            'data' => [
                'type' => 'balances',
                'id' => $wallet->address,
                'attributes' => [
                    'address' => $balance->address,
                    'balance_wei' => $balance->balanceWei,
                    'balance_eth' => $balance->balanceEth,
                ],
            ],
        ]);
    }
}
