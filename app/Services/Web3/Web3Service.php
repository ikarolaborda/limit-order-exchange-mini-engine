<?php

declare(strict_types=1);

namespace App\Services\Web3;

use App\Services\Web3\DTO\WalletDTO;
use App\Services\Web3\DTO\BalanceDTO;
use App\Services\Web3\DTO\TransactionDTO;
use App\Services\Web3\DTO\TransactionStatusDTO;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class Web3Service implements Web3ServiceInterface
{
    private PendingRequest $client;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
        $this->client = Http::baseUrl($this->baseUrl)
            ->withHeaders(['X-API-Key' => $this->apiKey])
            ->timeout(30)
            ->retry(3, 100);
    }

    public function createWallet(string $password): WalletDTO
    {
        $response = $this->client->post('/api/v1/wallets', [
            'password' => $password,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to create wallet: ' . ($response->json('error') ?? $response->body())
            );
        }

        return WalletDTO::fromArray($response->json());
    }

    public function getBalance(string $address): BalanceDTO
    {
        $response = $this->client->get("/api/v1/wallets/{$address}/balance");

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to get balance: ' . ($response->json('error') ?? $response->body())
            );
        }

        return BalanceDTO::fromArray($response->json());
    }

    public function listWallets(): array
    {
        $response = $this->client->get('/api/v1/wallets');

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to list wallets: ' . ($response->json('error') ?? $response->body())
            );
        }

        return $response->json('wallets') ?? [];
    }

    public function sendTransaction(string $from, string $to, string $amount, string $password): TransactionDTO
    {
        $response = $this->client->post('/api/v1/transactions', [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'password' => $password,
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to send transaction: ' . ($response->json('error') ?? $response->body())
            );
        }

        return TransactionDTO::fromArray($response->json());
    }

    public function getTransactionStatus(string $txHash): TransactionStatusDTO
    {
        $response = $this->client->get("/api/v1/transactions/{$txHash}");

        if ($response->failed()) {
            throw new RuntimeException(
                'Failed to get transaction status: ' . ($response->json('error') ?? $response->body())
            );
        }

        return TransactionStatusDTO::fromArray($response->json());
    }

    public function isHealthy(): bool
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->timeout(5)
                ->get('/health');

            return $response->successful() && $response->json('status') === 'healthy';
        } catch (\Exception) {
            return false;
        }
    }
}
