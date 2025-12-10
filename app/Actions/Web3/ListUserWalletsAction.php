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
    path: '/api/web3/wallets',
    operationId: 'listWallets',
    description: 'Returns all Ethereum wallets belonging to the authenticated user, sorted by primary status and creation date.',
    summary: 'List user wallets',
    security: [['sanctum' => []]],
    tags: ['Web3'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of wallets',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'type', type: 'string', example: 'wallets'),
                                new OA\Property(property: 'id', type: 'string', example: '1'),
                                new OA\Property(
                                    property: 'attributes',
                                    properties: [
                                        new OA\Property(property: 'address', type: 'string'),
                                        new OA\Property(property: 'label', type: 'string', nullable: true),
                                        new OA\Property(property: 'is_primary', type: 'boolean'),
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
                            new OA\Property(property: 'total', type: 'integer', example: 2),
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
final class ListUserWalletsAction
{
    use AsAction;

    public function handle(User $user): Collection
    {
        return $user->wallets()->orderByDesc('is_primary')->orderBy('created_at')->get();
    }

    public function asController(Request $request): JsonResponse
    {
        $wallets = $this->handle($request->user());

        return response()->json([
            'data' => $wallets->map(fn ($wallet) => [
                'type' => 'wallets',
                'id' => (string) $wallet->id,
                'attributes' => [
                    'address' => $wallet->address,
                    'label' => $wallet->label,
                    'is_primary' => $wallet->is_primary,
                    'created_at' => $wallet->created_at->toIso8601String(),
                ],
            ]),
            'meta' => [
                'total' => $wallets->count(),
            ],
        ]);
    }
}
