<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
final class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $side = fake()->randomElement(['buy', 'sell']);
        $price = fake()->randomFloat(8, 40000, 60000);
        $amount = fake()->randomFloat(8, 0.001, 1);

        return [
            'user_id' => User::factory(),
            'symbol' => fake()->randomElement(['BTC', 'ETH']),
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'locked_usd' => $side === 'buy' ? $price * $amount * 1.015 : 0,
            'status' => Order::STATUS_OPEN,
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes): array => [
            'side' => 'buy',
            'locked_usd' => $attributes['price'] * $attributes['amount'] * 1.015,
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes): array => [
            'side' => 'sell',
            'locked_usd' => 0,
        ]);
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Order::STATUS_OPEN,
        ]);
    }

    public function filled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Order::STATUS_FILLED,
            'locked_usd' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Order::STATUS_CANCELLED,
            'locked_usd' => 0,
        ]);
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

