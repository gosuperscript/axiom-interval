<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval;

use Superscript\Interval\Interval;
use Superscript\Axiom\Extension;
use Superscript\Axiom\Interval\Types\IntervalType;
use Superscript\Axiom\Operators\Operator;
use Superscript\Axiom\Types\BooleanType;
use Superscript\Axiom\Types\NumberType;
use Superscript\Axiom\Types\Type;

/**
 * The interval package's contribution to a {@see \Superscript\Axiom\Dialect}:
 * the operator rules and literal registration that make {@see Interval} a
 * full citizen of the compiler.
 *
 * Compose it onto core and hand the dialect to an expression:
 *
 * ```php
 * $dialect = Dialect::core()->with(new IntervalExtension());
 * ```
 *
 * Two families of rules:
 *
 * - **Ordering against a number** (`interval > 3`): an interval is greater
 *   than a number when it lies entirely above it, and so on. The interval
 *   is the left operand, mirroring the notation a host writes.
 * - **Equality against another interval** (`interval == interval`): core
 *   refuses equality on opaque operands — object equality belongs to the
 *   package that owns the type — so the aliases are declared here, the
 *   negated ones carrying the negation in their evaluation, just as core
 *   does for its own equality aliases.
 */
final class IntervalExtension extends Extension
{
    public function operators(): array
    {
        $interval = new IntervalType();
        $number = new NumberType();
        $boolean = new BooleanType();

        return [
            Operator::infix('<')->takes($interval, $number)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, int|float $right) => $left->isLessThan(self::exact($right))),
            Operator::infix('<=')->takes($interval, $number)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, int|float $right) => $left->isLessThanOrEqualTo(self::exact($right))),
            Operator::infix('>')->takes($interval, $number)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, int|float $right) => $left->isGreaterThan(self::exact($right))),
            Operator::infix('>=')->takes($interval, $number)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, int|float $right) => $left->isGreaterThanOrEqualTo(self::exact($right))),

            Operator::infix('=')->takes($interval, $interval)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('==')->takes($interval, $interval)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('===')->takes($interval, $interval)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('!=')->takes($interval, $interval)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, Interval $right) => !$left->isEqualTo($right)),
            Operator::infix('!==')->takes($interval, $interval)->returns($boolean)
                ->evaluatesWith(fn(Interval $left, Interval $right) => !$left->isEqualTo($right)),
        ];
    }

    /**
     * @return array<class-string, callable(object): Type>
     */
    public function literals(): array
    {
        return [Interval::class => fn() => new IntervalType()];
    }

    /**
     * Renders a host scalar in the form an interval bound can be compared against.
     *
     * brick rejects floats throughout its arithmetic, because a binary float carries no exact
     * decimal value to compare with: the literal 0.1 is not one tenth. A float is therefore spelled
     * as the shortest decimal that round-trips back to it, so the comparison sees the number the
     * host actually holds.
     *
     * That spelling is `json_encode`'s, because it honours `serialize_precision` (-1). A `(string)`
     * cast honours `precision` instead — 14 significant digits by default — so it truncates and can
     * emit exponent notation: `(string) 123456789012345.67` gives `1.2345678901235E+14`, which sits
     * on the wrong side of a bound between the two. Ints are already exact.
     *
     * A non-finite float names no decimal at all; JSON_THROW_ON_ERROR refuses it rather than let a
     * meaningless bound through.
     */
    private static function exact(int|float $value): string|int
    {
        return is_float($value) ? json_encode($value, JSON_THROW_ON_ERROR) : $value;
    }
}
