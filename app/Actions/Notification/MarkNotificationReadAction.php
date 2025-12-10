<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/notifications/{notification}/read',
    operationId: 'markNotificationRead',
    description: 'Marks a specific notification as read for the authenticated user.',
    summary: 'Mark notification as read',
    security: [['sanctum' => []]],
    tags: ['Notifications'],
    parameters: [
        new OA\Parameter(
            name: 'notification',
            description: 'Notification UUID',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', format: 'uuid')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Notification marked as read',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'success', type: 'boolean', example: true),
                ]
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Notification not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')
        ),
    ]
)]
final class MarkNotificationReadAction
{
    use AsAction;

    public function asController(Request $request, string $notificationId): JsonResponse
    {
        $user = $request->user();

        $notification = $user->notifications()->find($notificationId);

        if ($notification === null) {
            return response()->json(
                ['message' => 'Notification not found.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
