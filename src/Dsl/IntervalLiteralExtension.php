<?php

declare(strict_types=1);

namespace Superscript\Axiom\Interval\Dsl;

use Superscript\Axiom\Dsl\DslLiteralExtension;

final readonly class IntervalLiteralExtension implements DslLiteralExtension
{
    private const INTERVAL_PATTERN = '/^[\[(].+\.\..+[\])]$/';

    public function canParse(string $input): bool
    {
        return (bool) preg_match(self::INTERVAL_PATTERN, $input);
    }

    public function canParsePattern(string $input): bool
    {
        return $this->canParse($input);
    }
}
