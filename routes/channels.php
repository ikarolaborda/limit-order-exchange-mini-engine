<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', fn (User $user, int $userId): ?array => $user->id === $userId
    ? ['id' => $user->id, 'name' => $user->name]
    : null
);

