<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Trade;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TradeTest extends TestCase
{
    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $trade = new Trade();
        $fillable = $trade->getFillable();

        $this->assertContains('buy_order_id', $fillable);
        $this->assertContains('sell_order_id', $fillable);
        $this->assertContains('symbol', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('fee', $fillable);
    }

    #[Test]
    public function it_casts_decimal_attributes(): void
    {
        $trade = new Trade();
        $casts = $trade->getCasts();

        $this->assertArrayHasKey('price', $casts);
        $this->assertArrayHasKey('amount', $casts);
        $this->assertArrayHasKey('fee', $casts);
        $this->assertSame('decimal:8', $casts['price']);
        $this->assertSame('decimal:8', $casts['amount']);
        $this->assertSame('decimal:8', $casts['fee']);
    }
}
