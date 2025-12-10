<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
final class AssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'symbol' => fake()->randomElement(['BTC', 'ETH']),
            'amount' => fake()->randomFloat(8, 0, 10),
            'locked_amount' => 0,
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

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }

    public function withLockedAmount(float $lockedAmount): static
    {
        return $this->state(fn (array $attributes): array => [
            'locked_amount' => $lockedAmount,
        ]);
    }
}
