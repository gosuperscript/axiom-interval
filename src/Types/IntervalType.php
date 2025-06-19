<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Types;

use Superscript\Interval\Interval;
use Superscript\Monads\Option\Some;
use Superscript\Monads\Result\Result;
use Superscript\Schema\Exceptions\TransformValueException;
use Superscript\Schema\Types\Type;

use function Superscript\Monads\Result\attempt;
use function Superscript\Monads\Result\Err;

/**
 * @implements Type<Interval>
 */
final readonly class IntervalType implements Type
{
    public function transform(mixed $value): Result
    {
        if (!is_string($value)) {
            return err(new TransformValueException(type: 'interval', value: $value));
        }

        return attempt(fn () => Interval::fromString($value))
            ->map(fn(Interval $interval) => new Some($interval))
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
