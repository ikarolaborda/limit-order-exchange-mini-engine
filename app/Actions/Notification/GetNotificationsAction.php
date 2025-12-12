<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/notifications',
    operationId: 'getNotifications',
    description: 'Returns the most recent notifications for the authenticated user. Notifications are sorted with unread first, then by creation date. Maximum 50 notifications returned.',
    summary: 'Get user notifications',
    security: [['sanctum' => []]],
    tags: ['Notifications'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of notifications',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'type', type: 'string', example: 'App\\Notifications\\OrderFilledNotification'),
                                new OA\Property(
                                    property: 'data',
                                    properties: [
                                        new OA\Property(property: 'side', type: 'string', enum: ['buy', 'sell']),
                                        new OA\Property(property: 'symbol', type: 'string', example: 'BTC'),
                                        new OA\Property(property: 'amount', type: 'string'),
                                        new OA\Property(property: 'price', type: 'string'),
                                    ],
                                    type: 'object'
                                ),
                                new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        )
                    ),
                    new OA\Property(
                        property: 'meta',
                        properties: [
                            new OA\Property(property: 'unread_count', type: 'integer', example: 3),
                        ],
                        type: 'object'
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
final class GetNotificationsAction
{
    use AsAction;

    public function asController(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('read_at', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'type' => $notification->type,
                'data' => $notification->data,
                'read_at' => $notification->read_at?->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
            ]);

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'data' => $notifications,
            'meta' => [
                'unread_count' => $unreadCount,
            ],
        ]);
    }
}
