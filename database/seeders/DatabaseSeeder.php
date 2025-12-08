<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Sanctum\PersonalAccessToken;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create 4 demo traders
        $traders = [
            [
                'name' => 'Alice Trader',
                'email' => 'trader1@example.com',
                'balance' => 100000,
                'btc' => 2.5,
                'eth' => 20,
            ],
            [
                'name' => 'Bob Trader',
                'email' => 'trader2@example.com',
                'balance' => 75000,
                'btc' => 1.5,
                'eth' => 15,
            ],
            [
                'name' => 'Charlie Trader',
                'email' => 'trader3@example.com',
                'balance' => 50000,
                'btc' => 3.0,
                'eth' => 10,
            ],
            [
                'name' => 'Diana Trader',
                'email' => 'trader4@example.com',
                'balance' => 125000,
                'btc' => 0.5,
                'eth' => 25,
            ],
        ];

        foreach ($traders as $index => $traderData) {
            $user = User::factory()->create([
                'name' => $traderData['name'],
                'email' => $traderData['email'],
                'password' => 'password', // Will be hashed by the model
                'balance' => $traderData['balance'],
            ]);

            // Create API token for each user
            PersonalAccessToken::create([
                'tokenable_type' => User::class,
                'tokenable_id' => $user->id,
                'name' => 'dev-token-' . ($index + 1),
                'token' => hash('sha256', 'dev-token-' . ($index + 1)),
                'abilities' => ['*'],
            ]);

            // Create assets for each user
            $user->assets()->createMany([
                ['symbol' => 'BTC', 'amount' => $traderData['btc'], 'locked_amount' => 0],
                ['symbol' => 'ETH', 'amount' => $traderData['eth'], 'locked_amount' => 0],
            ]);
        }

        // Create sample trades between users
        $this->call(TradeSeeder::class);
    }
}
