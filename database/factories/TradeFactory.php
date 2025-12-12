<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trade>
 */
final class TradeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(8, 40000, 60000);
        $amount = fake()->randomFloat(8, 0.001, 1);
        $fee = $price * $amount * 0.015;

        return [
            'buy_order_id' => Order::factory()->buy(),
            'sell_order_id' => Order::factory()->sell(),
            'symbol' => fake()->randomElement(['BTC', 'ETH']),
            'price' => $price,
            'amount' => $amount,
            'fee' => $fee,
        ];
    }

    public function btc(): static
    {
        return $this->state(fn (array $attributes): array => [
            'symbol' => 'BTC',
        ]);
    }

    public function eth(): static
    {
        return $this->state(fn (array $attributes): array => [
            'symbol' => 'ETH',
        ]);
    }
}
