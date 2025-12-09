<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\BlockchainTransaction;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

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
