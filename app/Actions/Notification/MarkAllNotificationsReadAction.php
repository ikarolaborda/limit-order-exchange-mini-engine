<?php

declare(strict_types=1);

namespace App\Actions\Notification;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

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
