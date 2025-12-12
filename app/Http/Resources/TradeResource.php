<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Trade
 */
final class TradeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'trades',
            'id' => (string) $this->id,
            'attributes' => [
                'symbol' => $this->symbol,
                'price' => $this->price,
                'amount' => $this->amount,
                'fee' => $this->fee,
                'created_at' => $this->created_at?->toIso8601String(),
            ],
            'relationships' => [
                'buy_order' => [
                    'data' => [
                        'type' => 'orders',
                        'id' => (string) $this->buy_order_id,
                    ],
                ],
                'sell_order' => [
                    'data' => [
                        'type' => 'orders',
                        'id' => (string) $this->sell_order_id,
                    ],
                ],
            ],
        ];
    }
}
