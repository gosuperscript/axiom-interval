<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Types;

use Brick\Math\BigNumber;
use Superscript\Interval\Interval;
use Superscript\Monads\Result\Result;
use Superscript\Axiom\Exceptions\TransformValueException;
use Superscript\Axiom\Types\Shapes\OpaqueShape;
use Superscript\Axiom\Types\Shapes\Shape;
use Superscript\Axiom\Types\Type;

use function Psl\Type\instance_of;
use function Psl\Type\non_empty_string;
use function Superscript\Monads\Option\Some;
use function Superscript\Monads\Result\attempt;
use function Superscript\Monads\Result\Err;
use function Superscript\Monads\Result\Ok;

/**
 * @implements Type<Interval>
 */
final readonly class IntervalType implements Type
{
    public function coerce(mixed $value): Result
    {
        return (match (true) {
            $value instanceof Interval => Ok($value),
            is_string($value) => attempt(fn() => Interval::fromString($value)),
            default => Err(new \UnhandledMatchError()),
        })
            ->map(fn(Interval $interval) => Some($interval))
            ->mapErr(fn() => new TransformValueException(type: 'interval', value: $value));
    }

    public function assert(mixed $value): Result
    {
        return $value instanceof Interval
            ? Ok(Some($value))
            : Err(new TransformValueException(type: 'interval', value: $value));
    }

    /**
     * Reads as the band it is, not as the notation it was written in: a
     * formatted interval ends up in front of a customer — on a quote, in a
     * document — where "[1,2)" says nothing. Endpoint openness is dropped,
     * because prose has no natural way to say it and no display has needed the
     * distinction; cast the value itself when the exact interval matters
     * ({@see Interval::__toString}).
     *
     * A half-bounded interval carries the PHP_INT_MIN/PHP_INT_MAX sentinel
     * {@see Interval::fromString} writes for the endpoint that was left out, so
     * the missing side becomes "or more"/"up to" rather than printing the
     * sentinel as if it were a real endpoint.
     */
    public function format(mixed $value): string
    {
        $interval = instance_of(Interval::class)->assert($value);

        $left = $interval->left->isEqualTo(PHP_INT_MIN) ? null : $interval->left;
        $right = $interval->right->isEqualTo(PHP_INT_MAX) ? null : $interval->right;

        return match (true) {
            $left !== null && $right !== null => sprintf('%s – %s', self::number($left), self::number($right)),
            $left !== null => sprintf('%s or more', self::number($left)),
            $right !== null => sprintf('up to %s', self::number($right)),
            default => 'any',
        };
    }

    /**
     * Grouped to the thousand and never padded: an endpoint keeps exactly the
     * decimals it was written with, so 50000 reads "50,000" and 1.5 stays "1.5".
     */
    private static function number(BigNumber $number): string
    {
        $decimal = $number->toBigDecimal();
        $formatter = new \NumberFormatter('en_GB', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimal->getScale());

        return non_empty_string()->assert($formatter->format($decimal->toFloat()));
    }

    /**
     * An interval is an object-valued domain type: nominal identity, no
     * structural fields for member access to reach. It relates only to
     * other intervals, and the operations it supports — ordering against a
     * number, equality against another interval — are contributed as
     * operator rules ({@see \Superscript\Axiom\Interval\IntervalExtension}),
     * the way every opaque type owns its own operations.
     */
    public function shape(): Shape
    {
        return new OpaqueShape('interval');
    }
}
