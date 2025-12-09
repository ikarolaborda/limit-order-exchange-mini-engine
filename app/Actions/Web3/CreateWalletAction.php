<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\User;
use App\Models\UserWallet;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

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
