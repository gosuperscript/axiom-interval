<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Tests\Dsl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Dsl\DslLiteralExtension;
use Superscript\Schema\Interval\Dsl\IntervalLiteralExtension;

#[CoversClass(IntervalLiteralExtension::class)]
class IntervalLiteralExtensionTest extends TestCase
{
    #[Test]
    public function it_implements_dsl_literal_extension(): void
    {
        $this->assertInstanceOf(DslLiteralExtension::class, new IntervalLiteralExtension());
    }

    #[DataProvider('canParseProvider')]
    #[Test]
    public function it_can_parse_interval_literals(string $input, bool $expected): void
    {
        $extension = new IntervalLiteralExtension();

        $this->assertSame($expected, $extension->canParse($input));
    }

    public static function canParseProvider(): array
    {
        return [
            'closed interval' => ['[1..2]', true],
            'open interval' => ['(1..2)', true],
            'right-open interval' => ['[1..2)', true],
            'left-open interval' => ['(1..2]', true],
            'list literal' => ['[1, 2]', false],
            'empty brackets' => ['[]', false],
            'single dot' => ['[1.2]', false],
        ];
    }

    #[DataProvider('canParseProvider')]
    #[Test]
    public function it_can_parse_interval_patterns(string $input, bool $expected): void
    {
        $extension = new IntervalLiteralExtension();

        $this->assertSame($expected, $extension->canParsePattern($input));
    }
}
