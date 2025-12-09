<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

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
