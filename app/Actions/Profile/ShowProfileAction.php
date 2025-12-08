<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

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

