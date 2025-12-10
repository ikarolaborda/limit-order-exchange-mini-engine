<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Actions\Activity\LogActivityAction;
use App\Models\User;
use App\Models\UserWallet;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/web3/wallets',
    operationId: 'createWallet',
    description: 'Creates a new Ethereum wallet for the authenticated user. The wallet is encrypted with the provided password and stored securely. The first wallet created becomes the primary wallet.',
    summary: 'Create a new Ethereum wallet',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['password'],
            properties: [
                new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'securepassword123'),
                new OA\Property(property: 'label', type: 'string', maxLength: 100, nullable: true, example: 'Trading Wallet'),
            ]
        )
    ),
    tags: ['Web3'],
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Wallet created successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        properties: [
                            new OA\Property(property: 'type', type: 'string', example: 'wallets'),
                            new OA\Property(property: 'id', type: 'string', example: '1'),
                            new OA\Property(
                                property: 'attributes',
                                properties: [
                                    new OA\Property(property: 'address', type: 'string', example: '0x742d35Cc6634C0532925a3b844Bc9e7595f...'),
                                    new OA\Property(property: 'label', type: 'string', nullable: true),
                                    new OA\Property(property: 'is_primary', type: 'boolean', example: true),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            ),
                        ],
                        type: 'object'
                    ),
                    new OA\Property(property: 'message', type: 'string', example: 'Wallet created successfully. Store your password securely - it cannot be recovered.'),
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
final class CreateWalletAction
{
    use AsAction;

    public function __construct(
        private readonly Web3ServiceInterface $web3Service,
    ) {}

    public function handle(User $user, string $password, ?string $label = null): UserWallet
    {
        $result = $this->web3Service->createWallet($password);

        $isFirst = $user->wallets()->count() === 0;

        return $user->wallets()->create([
            'address' => $result->address,
            'label' => $label,
            'is_primary' => $isFirst,
        ]);
    }

    public function asController(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'label' => 'nullable|string|max:100',
        ]);

        $wallet = $this->handle(
            $request->user(),
            $request->input('password'),
            $request->input('label'),
        );

        LogActivityAction::run(
            $request->user(),
            sprintf('Created Ethereum wallet: %s', substr($wallet->address, 0, 10) . '...'),
            $request
        );

        return response()->json([
            'data' => [
                'type' => 'wallets',
                'id' => (string) $wallet->id,
                'attributes' => [
                    'address' => $wallet->address,
                    'label' => $wallet->label,
                    'is_primary' => $wallet->is_primary,
                    'created_at' => $wallet->created_at->toIso8601String(),
                ],
            ],
            'message' => 'Wallet created successfully. Store your password securely - it cannot be recovered.',
        ], 201);
    }
}
