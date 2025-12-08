<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Asset;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AssetTest extends TestCase
{
    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $asset = new Asset();
        $fillable = $asset->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('symbol', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('locked_amount', $fillable);
    }

    #[Test]
    public function it_casts_decimal_attributes(): void
    {
        $asset = new Asset();
        $casts = $asset->getCasts();

        $this->assertArrayHasKey('amount', $casts);
        $this->assertArrayHasKey('locked_amount', $casts);
        $this->assertSame('decimal:8', $casts['amount']);
        $this->assertSame('decimal:8', $casts['locked_amount']);
    }
}
