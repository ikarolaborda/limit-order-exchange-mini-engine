<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User
 */
final class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lockedBalance = $this->orders()
            ->where('status', Order::STATUS_OPEN)
            ->where('side', 'buy')
            ->sum('locked_usd');

        return [
            'type' => 'users',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->email,
                'balance' => $this->balance,
                'locked_balance' => $lockedBalance,
            ],
            'relationships' => [
                'assets' => AssetResource::collection($this->whenLoaded('assets')),
            ],
            'links' => [
                'self' => route('api.profile'),
                'orders' => route('api.my-orders'),
            ],
        ];
    }
}
