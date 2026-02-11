<?php

namespace Superscript\Axiom\Interval\Tests\Operators;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psl\Type\Exception\AssertException;
use Superscript\Interval\Interval;
use Superscript\Axiom\Interval\Operators\IntervalOverloader;
use PHPUnit\Framework\TestCase;

#[CoversClass(IntervalOverloader::class)]
class IntervalOverloaderTest extends TestCase
{
    #[Test]
    #[DataProvider('comparisons')]
    public function it_evaluates_comparisons(string $left, string $operator, int|float $right, mixed $expected): void
    {
        $interval = Interval::fromString($left);
        $overloader = new IntervalOverloader();
        $this->assertTrue($overloader->supportsOverloading(left: $interval, right: $right, operator: $operator));
        $this->assertSame($expected, $overloader->evaluate(left: $interval, right: $right, operator: $operator)->unwrap());
    }

    public static function comparisons(): Generator
    {
        yield ['[2, 3]', '>', 1, true];
        yield ['[2, 3]', '>=', 2, true];
        yield ['[2, 3]', '>', 3, false];
        yield ['[2, 3]', '<', 2, false];
        yield ['[2, 3]', '<=', 3, true];
        yield ['[2, 3]', '<', 4, true];
    }

    #[Test]
    public function it_returns_err_for_invalid_operator(): void
    {
        $interval = Interval::fromString('[2, 3]');
        $overloader = new IntervalOverloader();
        $result = $overloader->evaluate(left: $interval, right: 2, operator: '==');
        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
        $this->assertSame('Unsupported operator: ==', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function it_returns_err_for_invalid_left_value(): void
    {
        $overloader = new IntervalOverloader();
        $this->assertFalse($overloader->supportsOverloading(left: 'invalid', right: 2, operator: '>'));
        $result = $overloader->evaluate(left: 'invalid', right: 2, operator: '>');
        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AssertException::class, $result->unwrapErr());
        $this->assertStringContainsString('Expected "Superscript\Interval\Interval", got "string"', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function it_returns_err_for_invalid_right_value(): void
    {
        $interval = Interval::fromString('[2, 3]');
        $overloader = new IntervalOverloader();
        $this->assertFalse($overloader->supportsOverloading(left: $interval, right: 'invalid', operator: '>'));
        $result = $overloader->evaluate(left: $interval, right: 'invalid', operator: '>');
        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(AssertException::class, $result->unwrapErr());
        $this->assertStringContainsString('Expected "float|int", got "string"', $result->unwrapErr()->getMessage());
    }

    #[Test]
    public function it_returns_err_for_unsupported_operator(): void
    {
        $interval = Interval::fromString('[2, 3]');
        $overloader = new IntervalOverloader();
        $this->assertFalse($overloader->supportsOverloading(left: $interval, right: 2, operator: '!='));
        $result = $overloader->evaluate(left: $interval, right: 2, operator: '!=');
        $this->assertTrue($result->isErr());
        $this->assertInstanceOf(\InvalidArgumentException::class, $result->unwrapErr());
        $this->assertSame('Unsupported operator: !=', $result->unwrapErr()->getMessage());
    }
}