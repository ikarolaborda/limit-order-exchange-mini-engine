<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/notifications/read-all',
    operationId: 'markAllNotificationsRead',
    description: 'Marks all unread notifications as read for the authenticated user.',
    summary: 'Mark all notifications as read',
    security: [['sanctum' => []]],
    tags: ['Notifications'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'All notifications marked as read',
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
    ]
)]
final class MarkAllNotificationsReadAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
