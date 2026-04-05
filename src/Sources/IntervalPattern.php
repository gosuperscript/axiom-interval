<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Sources;

use Superscript\Axiom\Sources\MatchPattern;
use Superscript\Interval\Interval;

final readonly class IntervalPattern implements MatchPattern
{
    public function __construct(
        public Interval $interval,
    ) {}
}
