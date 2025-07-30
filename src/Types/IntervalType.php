<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Types;

use Superscript\Interval\Interval;
use Superscript\Monads\Result\Result;
use Superscript\Schema\Exceptions\TransformValueException;
use Superscript\Schema\Types\Type;

use function Superscript\Monads\Option\Some;
use function Superscript\Monads\Result\attempt;
use function Superscript\Monads\Result\Err;
use function Superscript\Monads\Result\Ok;

/**
 * @implements Type<Interval>
 */
final readonly class IntervalType implements Type
{
    public function transform(mixed $value): Result
    {
        return (match (true) {
            $value instanceof Interval => Ok($value),
            is_string($value) => attempt(fn () => Interval::fromString($value)),
            default => Err(new \UnhandledMatchError()),
        })
            ->map(fn(Interval $interval) => Some($interval))
            ->mapErr(fn() => new TransformValueException(type: 'interval', value: $value));
    }

    public function compare(mixed $a, mixed $b): bool
    {
        return $a->isEqualTo($b);
    }

    public function format(mixed $value): string
    {
        return (string) $value;
    }
}
