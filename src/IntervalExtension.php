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
            Operator::infix('<')->signature($interval, $number)->returns($boolean)
                ->evaluate(fn(Interval $left, int|float $right) => $left->isLessThan($right)),
            Operator::infix('<=')->signature($interval, $number)->returns($boolean)
                ->evaluate(fn(Interval $left, int|float $right) => $left->isLessThanOrEqualTo($right)),
            Operator::infix('>')->signature($interval, $number)->returns($boolean)
                ->evaluate(fn(Interval $left, int|float $right) => $left->isGreaterThan($right)),
            Operator::infix('>=')->signature($interval, $number)->returns($boolean)
                ->evaluate(fn(Interval $left, int|float $right) => $left->isGreaterThanOrEqualTo($right)),

            Operator::infix('=')->signature($interval, $interval)->returns($boolean)
                ->evaluate(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('==')->signature($interval, $interval)->returns($boolean)
                ->evaluate(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('===')->signature($interval, $interval)->returns($boolean)
                ->evaluate(fn(Interval $left, Interval $right) => $left->isEqualTo($right)),
            Operator::infix('!=')->signature($interval, $interval)->returns($boolean)
                ->evaluate(fn(Interval $left, Interval $right) => !$left->isEqualTo($right)),
            Operator::infix('!==')->signature($interval, $interval)->returns($boolean)
                ->evaluate(fn(Interval $left, Interval $right) => !$left->isEqualTo($right)),
        ];
    }

    /**
     * @return array<class-string, callable(object): Type>
     */
    public function literals(): array
    {
        return [Interval::class => fn() => new IntervalType()];
    }
}
