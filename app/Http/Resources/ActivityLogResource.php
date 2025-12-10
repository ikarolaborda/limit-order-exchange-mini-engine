<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * @mixin \App\Models\ActivityLog
 */
#[OA\Schema(
    schema: 'ActivityLogResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'description', type: 'string', example: 'Logged in'),
        new OA\Property(property: 'ip_address', type: 'string', example: '127.0.0.1'),
        new OA\Property(property: 'user_agent', type: 'string', example: 'Mozilla/5.0...'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
final class ActivityLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
