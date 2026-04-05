<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Tests\Integration;

use Generator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Dsl\AxiomDsl;
use Superscript\Axiom\Dsl\CoreDslPlugin;
use Superscript\Axiom\Interval\Dsl\IntervalDslPlugin;
use Superscript\Axiom\Interval\Operators\IntervalOverloader;
use Superscript\Axiom\Operators\DefaultOverloader;
use Superscript\Axiom\Operators\OperatorOverloader;
use Superscript\Axiom\Operators\OverloaderManager;
use Superscript\Axiom\Resolvers\DelegatingResolver;
use Superscript\Axiom\Resolvers\InfixResolver;
use Superscript\Axiom\Resolvers\StaticResolver;
use Superscript\Axiom\Resolvers\SymbolResolver;
use Superscript\Axiom\Resolvers\UnaryResolver;
use Superscript\Axiom\Resolvers\ValueResolver;
use Superscript\Axiom\Sources\InfixExpression;
use Superscript\Axiom\Sources\StaticSource;
use Superscript\Axiom\Sources\SymbolSource;
use Superscript\Axiom\Sources\TypeDefinition;
use Superscript\Axiom\Sources\UnaryExpression;
use Superscript\Axiom\SymbolRegistry;
use Superscript\Interval\Interval;

#[CoversNothing]
class IntervalDslIntegrationTest extends TestCase
{
    private AxiomDsl $dsl;

    protected function setUp(): void
    {
        $this->dsl = AxiomDsl::fromPlugins(
            new CoreDslPlugin(),
            new IntervalDslPlugin(),
        );
    }

    private function resolve(string $source, string $symbol): mixed
    {
        $compilation = $this->dsl->evaluate($source);

        $resolver = new DelegatingResolver([
            StaticSource::class => StaticResolver::class,
            InfixExpression::class => InfixResolver::class,
            TypeDefinition::class => ValueResolver::class,
            SymbolSource::class => SymbolResolver::class,
            UnaryExpression::class => UnaryResolver::class,
        ]);

        $resolver->instance(OperatorOverloader::class, new OverloaderManager([
            new IntervalOverloader(),
            new DefaultOverloader(),
        ]));
        $resolver->instance(SymbolRegistry::class, $compilation->symbols);

        return $resolver->resolve(new SymbolSource($symbol))->unwrap()->unwrap();
    }

    #[Test]
    public function it_coerces_string_to_interval(): void
    {
        $result = $this->resolve('range: interval = "[1,5]"', 'range');

        $this->assertInstanceOf(Interval::class, $result);
        $this->assertTrue($result->isEqualTo(Interval::fromString('[1,5]')));
    }

    #[Test]
    #[DataProvider('comparisonProvider')]
    public function it_compares_interval_to_number(string $dsl, bool $expected): void
    {
        $source = <<<AXIOM
        range: interval = "[2,3]"
        result: bool = {$dsl}
        AXIOM;

        $this->assertSame($expected, $this->resolve($source, 'result'));
    }

    public static function comparisonProvider(): Generator
    {
        yield 'greater than (true)' => ['range > 1', true];
        yield 'less than (true)' => ['range < 4', true];
        yield 'greater than or equal (true)' => ['range >= 2', true];
        yield 'less than or equal (true)' => ['range <= 3', true];
        yield 'greater than (false)' => ['range > 3', false];
    }

    #[Test]
    public function it_uses_interval_in_conditional(): void
    {
        $source = <<<'AXIOM'
        range: interval = "[2,3]"
        factor: number = if range > 1 then 1.5 else 1.0
        AXIOM;

        $this->assertSame(1.5, $this->resolve($source, 'factor'));
    }

    #[Test]
    public function it_uses_interval_in_else_branch(): void
    {
        $source = <<<'AXIOM'
        range: interval = "[2,3]"
        factor: number = if range > 10 then 1.5 else 1.0
        AXIOM;

        $this->assertSame(1.0, $this->resolve($source, 'factor'));
    }

    #[Test]
    public function it_combines_interval_comparison_with_boolean_logic(): void
    {
        $source = <<<'AXIOM'
        range: interval = "[2,3]"
        in_bounds: bool = range >= 1 && range <= 5
        AXIOM;

        $this->assertSame(true, $this->resolve($source, 'in_bounds'));
    }
}
