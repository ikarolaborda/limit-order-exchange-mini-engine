<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/profile',
    operationId: 'getProfile',
    description: 'Retrieve the authenticated user\'s profile information including their USD balance and cryptocurrency asset holdings.',
    summary: 'Get user profile',
    security: [['sanctum' => []]],
    tags: ['Profile'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Profile retrieved successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProfileResource'),
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
final class ShowProfileAction
{
    use AsAction;

    public function handle(User $user): User
    {
        return $user->load('assets');
    }

    public function asController(Request $request): ProfileResource
    {
        $user = $this->handle($request->user());

        return new ProfileResource($user);
    }
}

