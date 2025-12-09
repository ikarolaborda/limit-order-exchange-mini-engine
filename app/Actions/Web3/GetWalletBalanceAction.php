<?php

declare(strict_types=1);

namespace App\Actions\Web3;

use App\Models\UserWallet;
use App\Services\Web3\DTO\BalanceDTO;
use App\Services\Web3\Web3ServiceInterface;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

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
