# Axiom Interval

A PHP library that extends [gosuperscript/axiom](https://github.com/gosuperscript/axiom) with support for [Interval](https://github.com/superscript/interval) types: a `Type` that validates and coerces intervals, and an `Extension` that teaches the compiler to compare them.

## Features

- **Interval Type**: Validate and coerce interval values at a program's boundary
- **Operator rules**: Compare an interval against a number (`>`, `<`, `>=`, `<=`) and two intervals for equality (`==`, `!=`), resolved and type-checked at compile time
- **Type Safety**: Built with PHP 8.4+ strict types
- **Format Support**: Parse intervals from string notation (e.g., `[1,2]`, `(1,2)`)

## Requirements

- PHP ^8.4
- ext-intl
- [gosuperscript/axiom](https://github.com/gosuperscript/axiom)
- [superscript/interval](https://github.com/superscript/interval) ^1.0.4

## Installation

Install via Composer:

```bash
composer require gosuperscript/axiom-interval
```

## Usage

### The interval type

`IntervalType` is an Axiom `Type`: it coerces and asserts interval values, formats them, and projects into the shape algebra as an opaque `interval` identity.

```php
use Superscript\Axiom\Interval\Types\IntervalType;

$type = new IntervalType();

// Coerce from a string or an Interval object
$interval = $type->coerce('[1,2]')->unwrap()->unwrap();

// Assert an existing Interval object (strict membership)
$type->assert($interval)->isOk(); // true

// Format back to string notation
$type->format($interval); // "[1,2]"
```

#### Interval notation

- `[1,2]` — Closed interval (includes both endpoints)
- `(1,2)` — Open interval (excludes both endpoints)
- `[1,2)` — Half-open (includes left, excludes right)
- `(1,2]` — Half-open (excludes left, includes right)

### The extension

`IntervalExtension` contributes the interval's operator rules and its literal registration. Compose it onto the core dialect and hand the dialect to an expression; the compiler resolves and type-checks every operator against it, and the compiled `Program` runs what it resolved — no runtime dispatch.

```php
use Superscript\Axiom\Dialect;
use Superscript\Axiom\Expression;
use Superscript\Axiom\Interval\IntervalExtension;
use Superscript\Axiom\Interval\Types\IntervalType;
use Superscript\Axiom\Sources\InfixExpression;
use Superscript\Axiom\Sources\StaticSource;
use Superscript\Axiom\Sources\SymbolSource;

$dialect = Dialect::core()->with(new IntervalExtension());

$expression = new Expression(
    new InfixExpression(new SymbolSource('range'), '>', new StaticSource(1)),
    dialect: $dialect,
    declarations: ['range' => new IntervalType()],
);

$program = $expression->compile()->unwrap();

$program(['range' => '[2,3]'])->unwrap()->unwrap(); // true — [2,3] lies above 1
```

#### Supported operations

- **Ordering against a number** — `interval > number`, `>=`, `<`, `<=` → boolean. The interval is the left operand.
- **Equality against another interval** — `interval == interval`, `!=` (and the `=`/`===`/`!==` aliases) → boolean. Core refuses equality on opaque operands, so the interval package owns its own.

Operand types that no rule accepts — `interval == number`, `interval + interval` — are refused at compile time with a named diagnostic, never at runtime.

## Development

### Testing

Run the full test suite:

```bash
composer test
```

Or run individual suites:

```bash
# Type checking with PHPStan
composer test:types

# Unit tests with PHPUnit (requires 100% code coverage)
composer test:unit

# Mutation testing with Infection
composer test:infection
```

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting:

```bash
vendor/bin/pint
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- Built on [superscript/interval](https://github.com/superscript/interval)
- Extends [gosuperscript/axiom](https://github.com/gosuperscript/axiom)
