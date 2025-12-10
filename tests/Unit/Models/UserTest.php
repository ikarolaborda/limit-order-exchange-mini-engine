<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $user = new User;
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('balance', $fillable);
    }

    #[Test]
    public function it_has_hidden_attributes(): void
    {
        $user = new User;
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    #[Test]
    public function it_casts_balance_as_decimal(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertArrayHasKey('balance', $casts);
        $this->assertSame('decimal:8', $casts['balance']);
    }
}
