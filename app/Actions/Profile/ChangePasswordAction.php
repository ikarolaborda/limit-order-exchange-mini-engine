<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Actions\Activity\LogActivityAction;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/profile/password',
    operationId: 'changePassword',
    description: 'Change the authenticated user\'s password. Requires current password verification.',
    summary: 'Change user password',
    security: [['sanctum' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['current_password', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'current_password', type: 'string', description: 'Current password'),
                new OA\Property(property: 'password', type: 'string', description: 'New password (min 8 characters)'),
                new OA\Property(property: 'password_confirmation', type: 'string', description: 'New password confirmation'),
            ]
        )
    ),
    tags: ['Profile'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Password changed successfully',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Password changed successfully'),
                ]
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
    ]
)]
final class ChangePasswordAction
{
    use AsAction;

    public function handle(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }

    public function asController(ChangePasswordRequest $request): JsonResponse
    {
        $this->handle($request->user(), $request->validated('password'));

        LogActivityAction::run($request->user(), 'Password changed', $request);

        return response()->json(['message' => 'Password changed successfully']);
    }
}
