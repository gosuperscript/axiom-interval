<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Patterns;

use Brick\Math\BigNumber;
use Superscript\Axiom\Patterns\PatternMatcher;
use Superscript\Axiom\Sources\MatchPattern;
use Superscript\Monads\Result\Result;
use Superscript\Axiom\Interval\Sources\IntervalPattern;

use function Superscript\Monads\Result\Ok;

final readonly class IntervalMatcher implements PatternMatcher
{
    public function supports(MatchPattern $pattern): bool
    {
        return $pattern instanceof IntervalPattern;
    }

    public function matches(MatchPattern $pattern, mixed $subjectValue): Result
    {
        if (! is_int($subjectValue) && ! is_float($subjectValue)) {
            return Ok(false);
        }

        assert($pattern instanceof IntervalPattern);

        $interval = $pattern->interval;
        $value = BigNumber::of($subjectValue);

        $leftOk = $interval->notation->isLeftOpen()
            ? $value->isGreaterThan($interval->left)
            : $value->isGreaterThanOrEqualTo($interval->left);

        $rightOk = $interval->notation->isRightOpen()
            ? $value->isLessThan($interval->right)
            : $value->isLessThanOrEqualTo($interval->right);

        return Ok($leftOk && $rightOk);
    }
}
