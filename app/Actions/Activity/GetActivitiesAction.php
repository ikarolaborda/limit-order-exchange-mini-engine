<?php

declare(strict_types=1);

namespace App\Actions\Activity;

use App\Http\Resources\ActivityLogResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/profile/activities',
    operationId: 'getActivities',
    description: 'Retrieve the authenticated user\'s activity log with the most recent activities first.',
    summary: 'Get user activity log',
    security: [['sanctum' => []]],
    tags: ['Profile'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Activities retrieved successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/ActivityLogResource')
                    ),
                ]
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
    ]
)]
final class GetActivitiesAction
{
    use AsAction;

    /**
     * @return Collection<int, \App\Models\ActivityLog>
     */
    public function handle(User $user, int $limit = 50): Collection
    {
        return $user->activities()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function asController(Request $request): AnonymousResourceCollection
    {
        $activities = $this->handle($request->user());

        return ActivityLogResource::collection($activities);
    }
}
