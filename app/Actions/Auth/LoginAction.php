<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Activity\LogActivityAction;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/auth/login',
    operationId: 'login',
    description: 'Login with email and password to receive a Bearer token for API authentication.',
    summary: 'Authenticate user',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
    ),
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Login successful',
            content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Invalid credentials',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
final class LoginAction
{
    use AsAction;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function handle(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
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

        LogActivityAction::run($user, 'Logged in', $request);

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response();
    }
}
