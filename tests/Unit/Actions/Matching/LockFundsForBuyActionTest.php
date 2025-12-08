<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Matching;

use App\Actions\Matching\LockFundsForBuyAction;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

final class LockFundsForBuyActionTest extends TestCase
{
    private OrderRepositoryInterface&MockInterface $orderRepository;
    private UserRepositoryInterface&MockInterface $userRepository;
    private LockFundsForBuyAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = Mockery::mock(OrderRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->action = new LockFundsForBuyAction(
            $this->orderRepository,
            $this->userRepository
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_creates_order_with_sufficient_balance(): void
    {
        $user = new User(['id' => 1, 'balance' => '100000']);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('findByIdWithLock')
            ->once()
            ->with(1)
            ->andReturn($user);

        $expectedOrder = new Order([
            'user_id' => 1,
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '0.5',
            'locked_usd' => '25375',
            'status' => Order::STATUS_OPEN,
        ]);

        $this->orderRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedOrder);

        $this->userRepository
            ->shouldReceive('decrementBalance')
            ->once()
            ->with(1, '25375');

        $result = $this->action->handle($user, [
            'symbol' => 'btc',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '0.5',
        ]);

        $this->assertSame($expectedOrder, $result);
    }

    public function test_handle_throws_exception_with_insufficient_balance(): void
    {
        $user = new User(['id' => 1, 'balance' => '100']);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('findByIdWithLock')
            ->once()
            ->with(1)
            ->andReturn($user);

        $this->expectException(ValidationException::class);

        $this->action->handle($user, [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '1',
        ]);
    }

    public function test_handle_calculates_correct_fee(): void
    {
        $user = new User(['id' => 1, 'balance' => '60000']);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('findByIdWithLock')
            ->once()
            ->andReturn($user);

        $this->userRepository
            ->shouldReceive('decrementBalance')
            ->once()
            ->with(1, Mockery::on(function (string $amount): bool {
                $expected = '50750';

                return $amount === $expected;
            }));

        $expectedOrder = new Order();

        $this->orderRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedOrder);

        $result = $this->action->handle($user, [
            'symbol' => 'BTC',
            'side' => 'buy',
            'price' => '50000',
            'amount' => '1',
        ]);

        $this->assertSame($expectedOrder, $result);
    }
}

