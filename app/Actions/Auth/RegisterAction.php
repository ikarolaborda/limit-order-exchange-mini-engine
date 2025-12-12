<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Actions\Activity\LogActivityAction;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Post(
    path: '/api/auth/register',
    operationId: 'register',
    description: 'Create a new user account and receive a Bearer token for API authentication.',
    summary: 'Register new user',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
    ),
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'Registration successful',
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterResponse')
        ),
        new OA\Response(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
        ),
    ]
)]
final class RegisterAction
{
    use AsAction;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, balance?: float}  $data
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

        LogActivityAction::run($user, 'Account created', $request);

        return UserResource::make($user)
            ->additional(['token' => $token])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
