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
            $user = User::firstOrCreate(
                ['email' => $traderData['email']],
                [
                    'name' => $traderData['name'],
                    'password' => bcrypt('password'),
                    'balance' => $traderData['balance'],
                    'email_verified_at' => now(),
                ]
            );

            PersonalAccessToken::firstOrCreate(
                [
                    'tokenable_type' => User::class,
                    'tokenable_id' => $user->id,
                    'name' => 'dev-token-'.($index + 1),
                ],
                [
                    'token' => hash('sha256', 'dev-token-'.($index + 1)),
                    'abilities' => ['*'],
                ]
            );

            foreach ([['symbol' => 'BTC', 'amount' => $traderData['btc']], ['symbol' => 'ETH', 'amount' => $traderData['eth']]] as $asset) {
                $user->assets()->firstOrCreate(
                    ['symbol' => $asset['symbol']],
                    ['amount' => $asset['amount'], 'locked_amount' => 0]
                );
            }
        }

        $this->call(TradeSeeder::class);
    }
}
