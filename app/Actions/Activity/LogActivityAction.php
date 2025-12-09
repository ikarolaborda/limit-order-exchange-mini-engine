<?php

declare(strict_types=1);

namespace App\Actions\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

final class LogActivityAction
{
    use AsAction;

    public function handle(User $user, string $description, ?Request $request = null): ActivityLog
    {
        $activityLog = ActivityLog::create([
            'user_id' => $user->id,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);

        Log::channel('activity')->info($description, [
            'activity_id' => $activityLog->id,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'timestamp' => $activityLog->created_at->toIso8601String(),
        ]);

        return $activityLog;
    }
}
