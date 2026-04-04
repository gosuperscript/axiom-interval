<?php

declare(strict_types=1);

namespace Superscript\Schema\Interval\Tests\Dsl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Superscript\Axiom\Dsl\FunctionRegistry;
use Superscript\Axiom\Dsl\OperatorRegistry;
use Superscript\Axiom\Dsl\TypeRegistry;
use Superscript\Schema\Interval\Dsl\IntervalDslPlugin;
use Superscript\Schema\Interval\Dsl\IntervalLiteralExtension;
use Superscript\Schema\Interval\Operators\IntervalOverloader;
use Superscript\Schema\Interval\Patterns\IntervalMatcher;
use Superscript\Schema\Interval\Types\IntervalType;

#[CoversClass(IntervalDslPlugin::class)]
#[UsesClass(IntervalMatcher::class)]
#[UsesClass(IntervalLiteralExtension::class)]
class IntervalDslPluginTest extends TestCase
{
    #[Test]
    public function it_registers_the_overlaps_operator(): void
    {
        $plugin = new IntervalDslPlugin();
        $registry = new OperatorRegistry();

        $plugin->operators($registry);

        $this->assertTrue($registry->isOperator('overlaps'));
        $this->assertTrue($registry->isKeywordOperator('overlaps'));
    }

    #[Test]
    public function it_registers_the_interval_type(): void
    {
        $plugin = new IntervalDslPlugin();
        $registry = new TypeRegistry();

        $plugin->types($registry);

        $this->assertTrue($registry->has('interval'));
        $this->assertInstanceOf(IntervalType::class, $registry->resolve('interval'));
    }

    #[Test]
    public function it_has_no_functions(): void
    {
        $plugin = new IntervalDslPlugin();
        $registry = new FunctionRegistry();

        $plugin->functions($registry);

        $this->assertEmpty($registry->all());
    }

    #[Test]
    public function it_provides_pattern_matchers(): void
    {
        $plugin = new IntervalDslPlugin();

        $this->assertCount(1, $plugin->patterns());
        $this->assertInstanceOf(IntervalMatcher::class, $plugin->patterns()[0]);
    }

    #[Test]
    public function it_provides_literal_extensions(): void
    {
        $plugin = new IntervalDslPlugin();

        $this->assertCount(1, $plugin->literals());
        $this->assertInstanceOf(IntervalLiteralExtension::class, $plugin->literals()[0]);
    }

    #[Test]
    public function it_provides_overloaders(): void
    {
        $plugin = new IntervalDslPlugin();

        $this->assertCount(1, $plugin->overloaders());
        $this->assertInstanceOf(IntervalOverloader::class, $plugin->overloaders()[0]);
    }
}
