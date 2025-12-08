<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginAction;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class LoginActionTest extends TestCase
{
    private UserRepositoryInterface&MockInterface $userRepository;
    private LoginAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->action = new LoginAction($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_handles_returns_user_with_valid_credentials(): void
    {
        $user = new User(['email' => 'john@example.com']);
        $user->setRawAttributes(['password' => 'hashed_password']);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('john@example.com')
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->with('password123', 'hashed_password')
            ->andReturn(true);

        $result = $this->action->handle([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame($user, $result);
    }

    #[Test]
    public function it_throws_exception_when_user_not_found(): void
    {
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with('unknown@example.com')
            ->andReturn(null);

        $this->expectException(ValidationException::class);

        $this->action->handle([
            'email' => 'unknown@example.com',
            'password' => 'password123',
        ]);
    }

    #[Test]
    public function it_throws_exception_with_invalid_password(): void
    {
        $user = new User(['email' => 'john@example.com']);
        $user->setRawAttributes(['password' => 'hashed_password']);

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->andReturn($user);

        Hash::shouldReceive('check')
            ->once()
            ->with('wrong_password', 'hashed_password')
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $this->action->handle([
            'email' => 'john@example.com',
            'password' => 'wrong_password',
        ]);
    }
}

