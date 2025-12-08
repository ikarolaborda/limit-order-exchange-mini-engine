<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

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

        $revokeAll = $request->boolean('revoke_all', false);
        $count = $this->handle($user, $revokeAll);

        return response()->json([
            'message' => 'Logged out successfully',
            'tokens_revoked' => $count,
        ]);
    }
}

