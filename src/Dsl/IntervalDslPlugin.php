<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Dsl;

use Superscript\Axiom\Dsl\Associativity;
use Superscript\Axiom\Dsl\DslLiteralExtension;
use Superscript\Axiom\Dsl\DslPlugin;
use Superscript\Axiom\Dsl\FunctionRegistry;
use Superscript\Axiom\Dsl\OperatorRegistry;
use Superscript\Axiom\Dsl\TypeRegistry;
use Superscript\Axiom\Operators\OperatorOverloader;
use Superscript\Axiom\Patterns\PatternMatcher;
use Superscript\Axiom\Interval\Operators\IntervalOverloader;
use Superscript\Axiom\Interval\Patterns\IntervalMatcher;
use Superscript\Axiom\Interval\Types\IntervalType;

final class IntervalDslPlugin implements DslPlugin
{
    public function operators(OperatorRegistry $operators): void
    {
        $operators->register('overlaps', 5, Associativity::Left, isKeyword: true);
    }

    public function types(TypeRegistry $types): void
    {
        $types->register('interval', fn () => new IntervalType());
    }

    public function functions(FunctionRegistry $functions): void {}

    /** @return list<PatternMatcher> */
    public function patterns(): array
    {
        return [new IntervalMatcher()];
    }

    /** @return list<DslLiteralExtension> */
    public function literals(): array
    {
        return [new IntervalLiteralExtension()];
    }

    /** @return list<OperatorOverloader> */
    public function overloaders(): array
    {
        return [new IntervalOverloader()];
    }
}
