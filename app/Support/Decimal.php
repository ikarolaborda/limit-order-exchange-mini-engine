<?php

declare(strict_types=1);

namespace App\Support;

final readonly class Decimal
{
    private const int SCALE = 8;

    public function __construct(
        private string $value,
    ) {}

    public static function from(string|float|int $value): self
    {
        return new self(self::format(number_format((float) $value, self::SCALE, '.', '')));
    }

    public function mul(self|string $other): self
    {
        $otherValue = $other instanceof self ? $other->value : $other;

        return new self(self::format(bcmul($this->value, $otherValue, self::SCALE)));
    }

    public function add(self|string $other): self
    {
        $otherValue = $other instanceof self ? $other->value : $other;

        return new self(self::format(bcadd($this->value, $otherValue, self::SCALE)));
    }

    public function sub(self|string $other): self
    {
        $otherValue = $other instanceof self ? $other->value : $other;

        return new self(self::format(bcsub($this->value, $otherValue, self::SCALE)));
    }

    public function compare(self|string $other): int
    {
        $otherValue = $other instanceof self ? $other->value : $other;

        return bccomp($this->value, $otherValue, self::SCALE);
    }

    public function isGreaterThan(self|string $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function isGreaterThanOrEqual(self|string $other): bool
    {
        return $this->compare($other) >= 0;
    }

    public function isLessThan(self|string $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function isLessThanOrEqual(self|string $other): bool
    {
        return $this->compare($other) <= 0;
    }

    public function isZero(): bool
    {
        return $this->compare('0') === 0;
    }

    public function isNegative(): bool
    {
        return $this->compare('0') < 0;
    }

    public function isPositive(): bool
    {
        return $this->compare('0') > 0;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private static function format(string $value): string
    {
        $trimmed = rtrim(rtrim($value, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }
}
