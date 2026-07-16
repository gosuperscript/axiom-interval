<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Types;

use Superscript\Interval\Interval;
use Superscript\Monads\Result\Result;
use Superscript\Axiom\Exceptions\TransformValueException;
use Superscript\Axiom\Types\Shapes\OpaqueShape;
use Superscript\Axiom\Types\Shapes\Shape;
use Superscript\Axiom\Types\Type;

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

    public function format(mixed $value): string
    {
        return (string) $value;
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
