<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class LoginAction
{
    use AsAction;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param array{email: string, password: string} $credentials
     */
    public function handle(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if ($user === null || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user;
    }

    public function asController(LoginRequest $request): JsonResponse
    {
        $user = $this->handle($request->validated());
        $token = $user->createToken('api-token')->plainTextToken;

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response();
    }
}

