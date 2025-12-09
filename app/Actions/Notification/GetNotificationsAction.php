<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

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
