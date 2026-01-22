<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Operators;

use Superscript\Interval\Interval;
use Superscript\Axiom\Operators\OperatorOverloader;

use function Psl\Type\float;
use function Psl\Type\instance_of;
use function Psl\Type\int;
use function Psl\Type\union;

final readonly class IntervalOverloader implements OperatorOverloader
{
    public function supportsOverloading(mixed $left, mixed $right, string $operator): bool
    {
        return $left instanceof Interval && is_numeric($right) && in_array($operator, ['>', '<', '>=', '<=']);
    }

    /**
     * Evaluates the comparison between two intervals based on the operator.
     *
     * @param  Interval  $left  The left interval.
     * @param  int|float  $right  The right interval.
     * @param  string  $operator  The operator to use for comparison.
     * @return bool Returns true or false based on the comparison.
     */
    public function evaluate(mixed $left, mixed $right, string $operator): mixed
    {
        instance_of(Interval::class)->assert($left);
        union(float(), int())->assert($right);

        return match ($operator) {
            '<' => $left->isLessThan($right),
            '<=' => $left->isLessThanOrEqualTo($right),
            '>' => $left->isGreaterThan($right),
            '>=' => $left->isGreaterThanOrEqualTo($right),
            default => throw new \InvalidArgumentException("Unsupported operator: $operator"),
        };
    }
}
