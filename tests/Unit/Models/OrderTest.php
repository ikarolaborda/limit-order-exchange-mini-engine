<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    #[Test]
    public function it_has_status_constants_defined(): void
    {
        $this->assertSame(1, Order::STATUS_OPEN);
        $this->assertSame(2, Order::STATUS_FILLED);
        $this->assertSame(3, Order::STATUS_CANCELLED);
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $order = new Order();
        $fillable = $order->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('symbol', $fillable);
        $this->assertContains('side', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('locked_usd', $fillable);
        $this->assertContains('status', $fillable);
    }

    #[Test]
    public function it_casts_decimal_attributes(): void
    {
        $order = new Order([
            'price' => '50000.12345678',
            'amount' => '0.5',
            'locked_usd' => '25000',
        ]);

        $casts = $order->getCasts();

        $this->assertArrayHasKey('price', $casts);
        $this->assertArrayHasKey('amount', $casts);
        $this->assertArrayHasKey('locked_usd', $casts);
    }
}
