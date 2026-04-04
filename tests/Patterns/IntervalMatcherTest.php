<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Tests\Patterns;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Sources\MatchPattern;
use Superscript\Interval\Interval;
use Superscript\Schema\Interval\Patterns\IntervalMatcher;
use Superscript\Schema\Interval\Sources\IntervalPattern;

#[CoversClass(IntervalMatcher::class)]
#[UsesClass(IntervalPattern::class)]
class IntervalMatcherTest extends TestCase
{
    #[Test]
    public function it_supports_interval_patterns(): void
    {
        $matcher = new IntervalMatcher();

        $this->assertTrue($matcher->supports(new IntervalPattern(Interval::fromString('[1, 10]'))));
    }

    #[Test]
    public function it_does_not_support_other_patterns(): void
    {
        $matcher = new IntervalMatcher();

        $this->assertFalse($matcher->supports($this->createMock(MatchPattern::class)));
    }

    #[DataProvider('containmentProvider')]
    #[Test]
    public function it_matches_values_within_interval(string $interval, int|float $value, bool $expected): void
    {
        $matcher = new IntervalMatcher();
        $pattern = new IntervalPattern(Interval::fromString($interval));

        $this->assertSame($expected, $matcher->matches($pattern, $value)->unwrap());
    }

    public static function containmentProvider(): array
    {
        return [
            // Closed [1, 10] — includes both endpoints
            'closed: inside' => ['[1, 10]', 5, true],
            'closed: left boundary' => ['[1, 10]', 1, true],
            'closed: right boundary' => ['[1, 10]', 10, true],
            'closed: below' => ['[1, 10]', 0, false],
            'closed: above' => ['[1, 10]', 11, false],

            // Open (1, 10) — excludes both endpoints
            'open: inside' => ['(1, 10)', 5, true],
            'open: left boundary' => ['(1, 10)', 1, false],
            'open: right boundary' => ['(1, 10)', 10, false],

            // Left-open (1, 10] — excludes left, includes right
            'left-open: left boundary' => ['(1, 10]', 1, false],
            'left-open: right boundary' => ['(1, 10]', 10, true],
            'left-open: inside' => ['(1, 10]', 5, true],

            // Right-open [1, 10) — includes left, excludes right
            'right-open: left boundary' => ['[1, 10)', 1, true],
            'right-open: right boundary' => ['[1, 10)', 10, false],
            'right-open: inside' => ['[1, 10)', 5, true],

            // Float values
            'float: inside closed' => ['[0, 100]', 50.5, true],
            'float: inside open' => ['(0, 1)', 0.5, true],
        ];
    }

    #[Test]
    public function it_returns_false_for_non_numeric_values(): void
    {
        $matcher = new IntervalMatcher();
        $pattern = new IntervalPattern(Interval::fromString('[1, 10]'));

        $this->assertSame(false, $matcher->matches($pattern, 'hello')->unwrap());
        $this->assertSame(false, $matcher->matches($pattern, null)->unwrap());
        $this->assertSame(false, $matcher->matches($pattern, true)->unwrap());
        $this->assertSame(false, $matcher->matches($pattern, [])->unwrap());
    }
}
