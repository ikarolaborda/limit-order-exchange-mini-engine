<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\BlockchainTransaction;
use App\Models\User;
use App\Models\UserWallet;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

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

        if (!$wallet) {
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
