<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\Decimal;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DecimalTest extends TestCase
{
    #[Test]
    #[DataProvider('additionProvider')]
    public function it_performs_addition(string $a, string $b, string $expected): void
    {
        $decimal = Decimal::from($a);
        $result = $decimal->add($b);

        $this->assertSame($expected, $result->toString());
    }

    public static function additionProvider(): array
    {
        return [
            ['1', '1', '2'],
            ['0.5', '0.5', '1'],
            ['10.12345678', '5.87654322', '16'],
            ['0', '0', '0'],
            ['100', '0.00000001', '100.00000001'],
        ];
    }

    #[Test]
    #[DataProvider('subtractionProvider')]
    public function it_performs_subtraction(string $a, string $b, string $expected): void
    {
        $decimal = Decimal::from($a);
        $result = $decimal->sub($b);

        $this->assertSame($expected, $result->toString());
    }

    public static function subtractionProvider(): array
    {
        return [
            ['2', '1', '1'],
            ['1', '0.5', '0.5'],
            ['10', '10', '0'],
            ['100.00000001', '0.00000001', '100'],
        ];
    }

    #[Test]
    #[DataProvider('multiplicationProvider')]
    public function it_performs_multiplication(string $a, string $b, string $expected): void
    {
        $decimal = Decimal::from($a);
        $result = $decimal->mul($b);

        $this->assertSame($expected, $result->toString());
    }

    public static function multiplicationProvider(): array
    {
        return [
            ['2', '3', '6'],
            ['0.5', '2', '1'],
            ['50000', '0.5', '25000'],
            ['100', '1.015', '101.5'],
            ['0', '100', '0'],
        ];
    }

    #[Test]
    #[DataProvider('comparisonProvider')]
    public function it_performs_comparison(string $a, string $b, int $expected): void
    {
        $decimal = Decimal::from($a);
        $result = $decimal->compare($b);

        $this->assertSame($expected, $result);
    }

    public static function comparisonProvider(): array
    {
        return [
            ['1', '2', -1],
            ['2', '1', 1],
            ['1', '1', 0],
            ['0.00000001', '0.00000002', -1],
            ['100', '100.00000000', 0],
        ];
    }

    #[Test]
    public function it_checks_is_greater_than(): void
    {
        $decimal = Decimal::from('10');

        $this->assertTrue($decimal->isGreaterThan('5'));
        $this->assertFalse($decimal->isGreaterThan('10'));
        $this->assertFalse($decimal->isGreaterThan('15'));
    }

    #[Test]
    public function it_checks_is_less_than(): void
    {
        $decimal = Decimal::from('10');

        $this->assertFalse($decimal->isLessThan('5'));
        $this->assertFalse($decimal->isLessThan('10'));
        $this->assertTrue($decimal->isLessThan('15'));
    }

    #[Test]
    public function it_checks_is_zero(): void
    {
        $this->assertTrue(Decimal::from('0')->isZero());
        $this->assertTrue(Decimal::from('0.00000000')->isZero());
        $this->assertFalse(Decimal::from('0.00000001')->isZero());
    }

    #[Test]
    public function it_checks_is_positive(): void
    {
        $this->assertTrue(Decimal::from('1')->isPositive());
        $this->assertTrue(Decimal::from('0.00000001')->isPositive());
        $this->assertFalse(Decimal::from('0')->isPositive());
        $this->assertFalse(Decimal::from('-1')->isPositive());
    }

    #[Test]
    public function it_checks_is_negative(): void
    {
        $this->assertFalse(Decimal::from('1')->isNegative());
        $this->assertFalse(Decimal::from('0')->isNegative());
        $this->assertTrue(Decimal::from('-1')->isNegative());
    }

    #[Test]
    public function it_converts_to_string(): void
    {
        $decimal = Decimal::from('100.50');

        $this->assertSame('100.5', $decimal->toString());
        $this->assertSame('100.5', (string) $decimal);
    }

    #[Test]
    public function it_creates_from_different_types(): void
    {
        $this->assertSame('100', Decimal::from(100)->toString());
        $this->assertSame('100.5', Decimal::from(100.5)->toString());
        $this->assertSame('100.5', Decimal::from('100.5')->toString());
    }

    #[Test]
    public function it_supports_chained_operations(): void
    {
        $result = Decimal::from('100')
            ->mul('1.015')
            ->add('10')
            ->sub('5');

        $this->assertSame('106.5', $result->toString());
    }

    #[Test]
    public function it_supports_decimal_operations_with_decimal_objects(): void
    {
        $a = Decimal::from('100');
        $b = Decimal::from('50');

        $this->assertSame('150', $a->add($b)->toString());
        $this->assertSame('50', $a->sub($b)->toString());
        $this->assertSame('5000', $a->mul($b)->toString());
        $this->assertSame(1, $a->compare($b));
    }
}
