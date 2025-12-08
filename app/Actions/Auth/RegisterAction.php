<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\Response;

final class RegisterAction
{
    use AsAction;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param array{name: string, email: string, password: string, balance?: float} $data
     */
    public function handle(array $data): User
    {
        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'balance' => $data['balance'] ?? 0,
        ]);
    }

    public function asController(RegisterRequest $request): JsonResponse
    {
        $user = $this->handle($request->validated());
        $token = $user->createToken('api-token')->plainTextToken;

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}

