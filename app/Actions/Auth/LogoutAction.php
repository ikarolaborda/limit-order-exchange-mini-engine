<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Activity\LogActivityAction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/auth/logout',
    operationId: 'logout',
    description: 'Revoke the current API token or all tokens for the authenticated user.',
    summary: 'Logout user',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: false,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'revoke_all',
                    description: 'If true, revokes all tokens for the user',
                    type: 'boolean',
                    example: false
                ),
            ]
        )
    ),
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Logout successful',
            content: new OA\JsonContent(ref: '#/components/schemas/LogoutResponse')
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
    ]
)]
final class LogoutAction
{
    use AsAction;

    public function handle(User $user, bool $revokeAll = false): int
    {
        if ($revokeAll) {
            return $user->tokens()->delete();
        }

        $user->currentAccessToken()?->delete();

        return 1;
    }

    public function asController(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Already logged out'], Response::HTTP_OK);
        }

        LogActivityAction::run($user, 'Logged out', $request);

        $revokeAll = $request->boolean('revoke_all', false);
        $count = $this->handle($user, $revokeAll);

        return response()->json([
            'message' => 'Logged out successfully',
            'tokens_revoked' => $count,
        ]);
    }
}
