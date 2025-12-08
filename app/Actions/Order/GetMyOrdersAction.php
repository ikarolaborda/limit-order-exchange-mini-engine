<?php

declare(strict_types=1);

namespace App\Actions\Order;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Http\Resources\OrderCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

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

