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
use Superscript\Axiom\Types\IdentityLiteralType;
use Superscript\Axiom\Types\UnionType;

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
        yield '[2,3] > 1.5' => ['[2,3]', '>', 1.5, true];
        yield '[2,3] < 3.5' => ['[2,3]', '<', 3.5, true];
        // Past 14 significant digits a (string) cast reads 123456789012345.67 as
        // 1.2345678901235E+14 — 123456789012350 — which lands above this bound and flips the answer.
        yield 'a bound between the float and its truncation' => [
            '[123456789012346,123456789012400]',
            '>',
            123456789012345.67,
            true,
        ];
    }

    #[Test]
    #[DataProvider('equalities')]
    public function it_compares_two_intervals_for_equality(string $left, string $operator, string $right, bool $expected): void
    {
        $source = new InfixExpression(new SymbolSource('left'), $operator, new SymbolSource('right'));

        $result = $this->evaluate(
            $source,
            ['left' => new IntervalType(), 'right' => new IntervalType()],
            ['left' => $left, 'right' => $right],
        );

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
    public function it_compares_two_spellings_of_one_literal_as_equal(): void
    {
        $source = new InfixExpression(
            new StaticSource(Interval::fromString('[1,2]')),
            '==',
            new StaticSource(Interval::fromString('[1.0,2.00]')),
        );

        $this->assertTrue($this->evaluate($source, [], []));
    }

    #[Test]
    #[DataProvider('equalityOperators')]
    public function it_refuses_to_compile_an_equality_between_literals_that_can_never_be_equal(string $operator): void
    {
        $source = new InfixExpression(
            new StaticSource(Interval::fromString('[1,2]')),
            $operator,
            new StaticSource(Interval::fromString('(1,2)')),
        );

        $result = (new Expression($source, dialect: $this->dialect()))->compile();

        $this->assertTrue($result->isErr());
    }

    public static function equalityOperators(): Generator
    {
        foreach (['=', '==', '===', '!=', '!=='] as $operator) {
            yield $operator => [$operator];
        }
    }

    #[Test]
    public function it_refuses_to_compile_a_band_compared_to_a_literal_outside_its_bands(): void
    {
        $band = new UnionType(
            new IdentityLiteralType(Interval::fromString('[5,10]'), new IntervalType()),
            new IdentityLiteralType(Interval::fromString('[10,20]'), new IntervalType()),
        );
        $outside = new InfixExpression(new SymbolSource('band'), '==', new StaticSource(Interval::fromString('[1,5]')));
        $inside = new InfixExpression(new SymbolSource('band'), '==', new StaticSource(Interval::fromString('[5.0,10]')));

        $this->assertTrue((new Expression($outside, dialect: $this->dialect(), declarations: ['band' => $band]))->compile()->isErr());
        $this->assertTrue($this->evaluate($inside, ['band' => $band], ['band' => '[5,10]']));
    }

    #[Test]
    public function it_registers_the_interval_literal(): void
    {
        // A bare interval literal compiles, typed as itself through the
        // extension's literal registration, and evaluates to itself.
        $program = (new Expression(
            new StaticSource(Interval::fromString('[1,2]')),
            dialect: $this->dialect(),
        ))->compile()->unwrap();

        $this->assertInstanceOf(IdentityLiteralType::class, $program->returns);
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
