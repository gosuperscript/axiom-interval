<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Tests\Sources;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Sources\MatchPattern;
use Superscript\Interval\Interval;
use Superscript\Schema\Interval\Sources\IntervalPattern;

#[CoversClass(IntervalPattern::class)]
class IntervalPatternTest extends TestCase
{
    #[Test]
    public function it_implements_match_pattern(): void
    {
        $pattern = new IntervalPattern(Interval::fromString('[1, 100]'));

        $this->assertInstanceOf(MatchPattern::class, $pattern);
    }

    #[Test]
    public function it_exposes_the_interval(): void
    {
        $interval = Interval::fromString('[1, 100]');
        $pattern = new IntervalPattern($interval);

        $this->assertTrue($interval->isEqualTo($pattern->interval));
    }
}
