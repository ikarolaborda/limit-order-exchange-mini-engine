<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

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
