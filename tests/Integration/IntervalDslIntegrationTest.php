<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Tests\Integration;

use Generator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Interval\Operators\IntervalOverloader;
use Superscript\Axiom\Interval\Patterns\IntervalMatcher;
use Superscript\Axiom\Interval\Sources\IntervalPattern;
use Superscript\Axiom\Interval\Types\IntervalType;
use Superscript\Axiom\Operators\OperatorOverloader;
use Superscript\Axiom\Patterns\LiteralMatcher;
use Superscript\Axiom\Patterns\WildcardMatcher;
use Superscript\Axiom\Resolvers\DelegatingResolver;
use Superscript\Axiom\Resolvers\InfixResolver;
use Superscript\Axiom\Resolvers\MatchResolver;
use Superscript\Axiom\Resolvers\StaticResolver;
use Superscript\Axiom\Resolvers\SymbolResolver;
use Superscript\Axiom\Resolvers\ValueResolver;
use Superscript\Axiom\Sources\InfixExpression;
use Superscript\Axiom\Sources\MatchArm;
use Superscript\Axiom\Sources\MatchExpression;
use Superscript\Axiom\Sources\StaticSource;
use Superscript\Axiom\Sources\SymbolSource;
use Superscript\Axiom\Sources\TypeDefinition;
use Superscript\Axiom\Sources\WildcardPattern;
use Superscript\Axiom\SymbolRegistry;
use Superscript\Interval\Interval;

#[CoversNothing]
class IntervalDslIntegrationTest extends TestCase
{
    private DelegatingResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DelegatingResolver([
            StaticSource::class => StaticResolver::class,
            InfixExpression::class => InfixResolver::class,
            TypeDefinition::class => ValueResolver::class,
            SymbolSource::class => SymbolResolver::class,
            MatchExpression::class => MatchResolver::class,
        ]);

        $this->resolver->instance(OperatorOverloader::class, new IntervalOverloader());
        $this->resolver->instance(SymbolRegistry::class, new SymbolRegistry());
    }

    #[Test]
    public function it_coerces_string_to_interval_through_resolver(): void
    {
        $source = new TypeDefinition(
            type: new IntervalType(),
            source: new StaticSource('[1,5]'),
        );

        $result = $this->resolver->resolve($source)->unwrap()->unwrap();

        $this->assertInstanceOf(Interval::class, $result);
    }

    #[Test]
    #[DataProvider('intervalComparisonProvider')]
    public function it_compares_interval_to_number(string $intervalStr, string $operator, int|float $right, bool $expected): void
    {
        $this->resolver->instance(SymbolRegistry::class, new SymbolRegistry([
            'value' => new StaticSource(Interval::fromString($intervalStr)),
        ]));

        $source = new InfixExpression(
            left: new SymbolSource('value'),
            operator: $operator,
            right: new StaticSource($right),
        );

        $result = $this->resolver->resolve($source)->unwrap()->unwrap();

        $this->assertSame($expected, $result);
    }

    public static function intervalComparisonProvider(): Generator
    {
        yield '[2,3] > 1 is true' => ['[2,3]', '>', 1, true];
        yield '[2,3] < 4 is true' => ['[2,3]', '<', 4, true];
        yield '[2,3] >= 2 is true' => ['[2,3]', '>=', 2, true];
        yield '[2,3] <= 3 is true' => ['[2,3]', '<=', 3, true];
        yield '[2,3] > 3 is false' => ['[2,3]', '>', 3, false];
    }

    #[Test]
    public function it_matches_number_against_interval_pattern(): void
    {
        $this->resolver->instance(MatchResolver::class, new MatchResolver(
            resolver: $this->resolver,
            matchers: [
                new IntervalMatcher(),
                new WildcardMatcher(),
                new LiteralMatcher(),
            ],
        ));

        $source = new MatchExpression(
            subject: new StaticSource(2.5),
            arms: [
                new MatchArm(
                    pattern: new IntervalPattern(Interval::fromString('[1,3]')),
                    expression: new StaticSource('in range'),
                ),
                new MatchArm(
                    pattern: new WildcardPattern(),
                    expression: new StaticSource('out of range'),
                ),
            ],
        );

        $result = $this->resolver->resolve($source)->unwrap()->unwrap();

        $this->assertSame('in range', $result);
    }

    #[Test]
    public function it_falls_through_to_wildcard_when_not_in_interval(): void
    {
        $this->resolver->instance(MatchResolver::class, new MatchResolver(
            resolver: $this->resolver,
            matchers: [
                new IntervalMatcher(),
                new WildcardMatcher(),
                new LiteralMatcher(),
            ],
        ));

        $source = new MatchExpression(
            subject: new StaticSource(5),
            arms: [
                new MatchArm(
                    pattern: new IntervalPattern(Interval::fromString('[1,3]')),
                    expression: new StaticSource('in range'),
                ),
                new MatchArm(
                    pattern: new WildcardPattern(),
                    expression: new StaticSource('out of range'),
                ),
            ],
        );

        $result = $this->resolver->resolve($source)->unwrap()->unwrap();

        $this->assertSame('out of range', $result);
    }

    #[Test]
    public function it_coerces_and_compares_in_single_expression(): void
    {
        $source = new InfixExpression(
            left: new TypeDefinition(
                type: new IntervalType(),
                source: new StaticSource('[10,20]'),
            ),
            operator: '>',
            right: new StaticSource(5),
        );

        $result = $this->resolver->resolve($source)->unwrap()->unwrap();

        $this->assertSame(true, $result);
    }
}
