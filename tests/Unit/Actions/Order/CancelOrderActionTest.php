<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Order;

use App\Actions\Order\CancelOrderAction;
use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class CancelOrderActionTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $orderRepository;
    private UserRepositoryInterface&MockInterface $userRepository;
    private AssetRepositoryInterface&MockInterface $assetRepository;
    private CancelOrderAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->assetRepository = Mockery::mock(AssetRepositoryInterface::class);

        $this->action = new CancelOrderAction(
            $this->orderRepository,
            $this->userRepository,
            $this->assetRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cannot_cancel_order_owned_by_another_user(): void
    {
        $user = new User(['id' => 1]);
        $user->id = 1;

        $order = new Order([
            'user_id' => 2,
            'status' => Order::STATUS_OPEN,
        ]);

        $this->expectException(ValidationException::class);

        $this->action->handle($user, $order);
    }

    public function test_cannot_cancel_filled_order(): void
    {
        $user = new User(['id' => 1]);
        $user->id = 1;

        $order = new Order([
            'user_id' => 1,
            'status' => Order::STATUS_FILLED,
        ]);
        $order->user_id = 1;

        $this->expectException(ValidationException::class);

        $this->action->handle($user, $order);
    }

    public function test_cannot_cancel_already_cancelled_order(): void
    {
        $user = new User(['id' => 1]);
        $user->id = 1;

        $order = new Order([
            'user_id' => 1,
            'status' => Order::STATUS_CANCELLED,
        ]);
        $order->user_id = 1;

        $this->expectException(ValidationException::class);

        $this->action->handle($user, $order);
    }
}

