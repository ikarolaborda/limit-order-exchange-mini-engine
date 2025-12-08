<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
final class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'orders',
            'id' => (string) $this->id,
            'attributes' => [
                'symbol' => $this->symbol,
                'side' => $this->side,
                'price' => $this->price,
                'amount' => $this->amount,
                'locked_usd' => $this->locked_usd,
                'status' => $this->status,
                'status_label' => $this->getStatusLabel(),
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->user_id,
                    ],
                ],
            ],
            'links' => [
                'self' => route('api.orders.show', $this->id),
            ],
            'meta' => [
                'can_cancel' => $this->status === Order::STATUS_OPEN &&
                               $this->user_id === $request->user()?->id,
            ],
        ];
    }

    protected function getStatusLabel(): string
    {
        return match ($this->status) {
            Order::STATUS_OPEN => 'open',
            Order::STATUS_FILLED => 'filled',
            Order::STATUS_CANCELLED => 'cancelled',
            default => 'unknown',
        };
    }
}

