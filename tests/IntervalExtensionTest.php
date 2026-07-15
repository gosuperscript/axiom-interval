<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Tests;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Superscript\Interval\Interval;
use Superscript\Axiom\Dialect;
use Superscript\Axiom\Expression;
use Superscript\Axiom\Interval\IntervalExtension;
use Superscript\Axiom\Interval\Types\IntervalType;
use Superscript\Axiom\Source;
use Superscript\Axiom\Sources\InfixExpression;
use Superscript\Axiom\Sources\StaticSource;
use Superscript\Axiom\Sources\SymbolSource;
use Superscript\Axiom\Types\BooleanType;

#[CoversClass(IntervalExtension::class)]
#[UsesClass(IntervalType::class)]
class IntervalExtensionTest extends TestCase
{
    private function dialect(): Dialect
    {
        return Dialect::core()->with(new IntervalExtension());
    }

    /**
     * @param array<string, \Superscript\Axiom\Types\Type> $declarations
     * @param array<string, mixed> $bindings
     */
    private function evaluate(Source $source, array $declarations, array $bindings): mixed
    {
        $expression = new Expression($source, dialect: $this->dialect(), declarations: $declarations);

        // Every expression under test is boolean-typed; checking it here
        // pins the return type alongside the runtime value.
        $this->assertTrue($expression->check(new BooleanType())->isOk());

        return $expression->compile()->unwrap()($bindings)->unwrap()->unwrap();
    }

    #[Test]
    #[DataProvider('comparisons')]
    public function it_compares_an_interval_against_a_number(string $interval, string $operator, int|float $number, bool $expected): void
    {
        $source = new InfixExpression(new SymbolSource('range'), $operator, new StaticSource($number));

        $result = $this->evaluate($source, ['range' => new IntervalType()], ['range' => $interval]);

        $this->assertSame($expected, $result);
    }

    public static function comparisons(): Generator
    {
        yield '[2,3] > 1'  => ['[2,3]', '>', 1, true];
        yield '[2,3] > 3'  => ['[2,3]', '>', 3, false];
        yield '[2,3] >= 2' => ['[2,3]', '>=', 2, true];
        yield '[2,3] < 2'  => ['[2,3]', '<', 2, false];
        yield '[2,3] < 4'  => ['[2,3]', '<', 4, true];
        yield '[2,3] <= 3' => ['[2,3]', '<=', 3, true];
    }

    #[Test]
    #[DataProvider('equalities')]
    public function it_compares_two_intervals_for_equality(string $left, string $operator, string $right, bool $expected): void
    {
        $source = new InfixExpression(
            new StaticSource(Interval::fromString($left)),
            $operator,
            new StaticSource(Interval::fromString($right)),
        );

        $result = $this->evaluate($source, [], []);

        $this->assertSame($expected, $result);
    }

    public static function equalities(): Generator
    {
        yield '[1,2] = [1,2]'    => ['[1,2]', '=', '[1,2]', true];
        yield '[1,2] == [1,2]'   => ['[1,2]', '==', '[1,2]', true];
        yield '[1,2] === [1,2]'  => ['[1,2]', '===', '[1,2]', true];
        yield '[1,2] == (1,2)'   => ['[1,2]', '==', '(1,2)', false];
        yield '[1,2] != (1,2)'   => ['[1,2]', '!=', '(1,2)', true];
        yield '[1,2] !== [1,2]'  => ['[1,2]', '!==', '[1,2]', false];
    }

    #[Test]
    public function it_registers_the_interval_literal(): void
    {
        // A bare interval literal compiles (typed through the extension's
        // literal registration) and evaluates to itself.
        $program = (new Expression(
            new StaticSource(Interval::fromString('[1,2]')),
            dialect: $this->dialect(),
        ))->compile()->unwrap();

        $this->assertInstanceOf(IntervalType::class, $program->returns);
        $this->assertTrue($program()->unwrap()->unwrap()->isEqualTo(Interval::fromString('[1,2]')));
    }

    #[Test]
    public function it_refuses_to_compile_an_interval_compared_to_a_number_for_equality(): void
    {
        // No rule resolves interval == number: equality is interval-to-interval only.
        $source = new InfixExpression(new SymbolSource('range'), '==', new StaticSource(5));

        $result = (new Expression($source, dialect: $this->dialect(), declarations: ['range' => new IntervalType()]))->compile();

        $this->assertTrue($result->isErr());
    }

    #[Test]
    public function it_refuses_to_compile_an_unsupported_interval_operator(): void
    {
        // No rule resolves interval + interval.
        $source = new InfixExpression(
            new StaticSource(Interval::fromString('[1,2]')),
            '+',
            new StaticSource(Interval::fromString('[1,2]')),
        );

        $result = (new Expression($source, dialect: $this->dialect()))->compile();

        $this->assertTrue($result->isErr());
    }
}
