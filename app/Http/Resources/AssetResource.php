<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Asset
 */
final class AssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'assets',
            'id' => (string) $this->id,
            'attributes' => [
                'symbol' => $this->symbol,
                'amount' => $this->amount,
                'locked_amount' => $this->locked_amount,
            ],
        ];
    }
}
