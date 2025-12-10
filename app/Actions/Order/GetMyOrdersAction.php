<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Resources\OrderCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Get(
    path: '/api/my-orders',
    operationId: 'getMyOrders',
    description: 'Retrieve all orders placed by the authenticated user, including open, filled, and cancelled orders.',
    summary: 'Get my orders',
    security: [['sanctum' => []]],
    tags: ['Orders'],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Orders retrieved successfully',
            content: new OA\JsonContent(ref: '#/components/schemas/OrderCollection')
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'Unauthenticated',
            content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
        ),
    ]
)]
final class GetMyOrdersAction
{
    use AsAction;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    public function handle(User $user): Collection
    {
        return $this->orderRepository->getUserOrders($user->id);
    }

    public function asController(Request $request): OrderCollection
    {
        $orders = $this->handle($request->user());

        return new OrderCollection($orders);
    }
}
