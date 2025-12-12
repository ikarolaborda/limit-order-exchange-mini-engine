<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterAction;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RegisterActionTest extends TestCase
{
    private UserRepositoryInterface&MockInterface $userRepository;

    private RegisterAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->action = new RegisterAction($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_creates_user_with_hashed_password(): void
    {
        Hash::shouldReceive('make')
            ->once()
            ->with('password123')
            ->andReturn('hashed_password');

        // Allow isHashed calls for User model's hashed cast
        Hash::shouldReceive('isHashed')
            ->andReturn(true);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'balance' => 1000.0,
        ];

        $expectedUser = new User;
        $expectedUser->setRawAttributes([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password',
            'balance' => 1000.0,
        ]);

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['name'] === 'John Doe'
                    && $data['email'] === 'john@example.com'
                    && $data['password'] === 'hashed_password'
                    && $data['balance'] === 1000.0;
            }))
            ->andReturn($expectedUser);

        $result = $this->action->handle($userData);

        $this->assertSame($expectedUser, $result);
    }

    #[Test]
    public function it_uses_default_balance_when_not_provided(): void
    {
        Hash::shouldReceive('make')
            ->once()
            ->andReturn('hashed_password');

        Hash::shouldReceive('isHashed')
            ->andReturn(true);

        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
        ];

        $expectedUser = new User;

        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['balance'] === 0;
            }))
            ->andReturn($expectedUser);

        $result = $this->action->handle($userData);

        $this->assertInstanceOf(User::class, $result);
    }
}
