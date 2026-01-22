<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Tests\Types;

use Brick\Math\BigNumber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Superscript\Interval\Interval;
use Superscript\Interval\IntervalNotation;
use Superscript\Axiom\Exceptions\TransformValueException;
use Superscript\Axiom\Interval\Types\IntervalType;

#[CoversClass(IntervalType::class)]
class IntervalTypeTest extends TestCase
{
    #[DataProvider('coerceProvider')]
    #[Test]
    public function it_can_coerce_a_value(mixed $value, Interval $expected)
    {
        $type = new IntervalType();
        $this->assertTrue($type->coerce($value)->unwrap()->unwrap()->isEqualTo($expected));
    }

    public static function coerceProvider(): array
    {
        return [
            [new Interval(BigNumber::of(1), BigNumber::of(2), IntervalNotation::Closed), new Interval(BigNumber::of(1), BigNumber::of(2), IntervalNotation::Closed)],
            ['[1,2]', new Interval(BigNumber::of(1), BigNumber::of(2), IntervalNotation::Closed)],
        ];
    }

    #[Test]
    public function it_returns_err_if_it_fails_to_coerce(): void
    {
        $type = new IntervalType();
        $result = $type->coerce($value = 'foobar');
        $this->assertEquals(new TransformValueException(type: 'interval', value: $value), $result->unwrapErr());
        $this->assertEquals('Unable to transform into [interval] from [\'foobar\']', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function it_returns_err_if_value_is_not_a_string(): void
    {
        $type = new IntervalType();
        $result = $type->coerce(123);
        $this->assertEquals(new TransformValueException(type: 'interval', value: 123), $result->unwrapErr());
        $this->assertEquals('Unable to transform into [interval] from [123]', $result->unwrapErr()->getMessage());
    }

    #[DataProvider('assertProvider')]
    #[Test]
    public function it_can_assert_a_value(mixed $value, bool $shouldPass)
    {
        $type = new IntervalType();
        $result = $type->assert($value);
        $this->assertSame($shouldPass, $result->isOk());
    }

    public static function assertProvider(): array
    {
        return [
            [new Interval(BigNumber::of(1), BigNumber::of(2), IntervalNotation::Closed), true],
            ['[1,2]', false],
            [123, false],
        ];
    }

    #[DataProvider('compareProvider')]
    #[Test]
    public function it_can_compare_two_values(string $a, string $b, bool $expected): void
    {
        $type = new IntervalType();
        $a = $type->coerce($a)->unwrap()->unwrap();
        $b = $type->coerce($b)->unwrap()->unwrap();
        $this->assertSame($expected, $type->compare($a, $b));
    }

    public static function compareProvider(): array
    {
        return [
            ['[1,2]', '[1,2]', true],
            ['(1,2)', '(1,2)', true],
            ['[1,2]', '(1,2)', false],
        ];
    }

    #[DataProvider('formatProvider')]
    #[Test]
    public function it_can_format_value(string $value, string $expected): void
    {
        $type = new IntervalType();
        $value = $type->coerce($value)->unwrap()->unwrap();
        $this->assertSame($expected, $type->format($value));
    }

    public static function formatProvider(): array
    {
        return [
            ['[1,2]', '[1,2]'],
            ['(1,2)', '(1,2)'],
        ];
    }
}
