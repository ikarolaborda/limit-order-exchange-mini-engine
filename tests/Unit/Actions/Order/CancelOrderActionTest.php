<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Order;

use App\Actions\Order\CancelOrderAction;
use App\Contracts\Repositories\AssetRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Order;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_cannot_cancel_filled_order(): void
    {
        $order = new Order([
            'user_id' => 1,
            'status' => Order::STATUS_FILLED,
        ]);
        $order->id = 1;
        $order->user_id = 1;

        $this->orderRepository
            ->shouldReceive('findByIdWithLock')
            ->with(1)
            ->once()
            ->andReturn($order);

        $this->expectException(ValidationException::class);

        $this->action->handle($order);
    }

    #[Test]
    public function it_cannot_cancel_already_cancelled_order(): void
    {
        $order = new Order([
            'user_id' => 1,
            'status' => Order::STATUS_CANCELLED,
        ]);
        $order->id = 2;
        $order->user_id = 1;

        $this->orderRepository
            ->shouldReceive('findByIdWithLock')
            ->with(2)
            ->once()
            ->andReturn($order);

        $this->expectException(ValidationException::class);

        $this->action->handle($order);
    }
}
