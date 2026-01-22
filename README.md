# Axiom Interval

A PHP library that extends [gosuperscript/schema](https://github.com/mannum/schema) with support for [Interval](https://github.com/superscript/interval) types, providing type-safe interval handling and operator overloading for schema validation.

## Features

- **Interval Type**: Transform and validate interval values in your schemas
- **Operator Overloading**: Compare intervals with numeric values using standard comparison operators (`>`, `<`, `>=`, `<=`)
- **Type Safety**: Built with PHP 8.4+ strict types
- **Format Support**: Parse intervals from string notation (e.g., `[1,2]`, `(1,2)`)
- **Comparison**: Compare intervals for equality

## Requirements

- PHP ^8.4
- ext-intl
- [gosuperscript/schema](https://github.com/mannum/schema)
- [superscript/interval](https://github.com/superscript/interval) ^1.0.4

## Installation

Install via Composer:

```bash
composer require gosuperscript/axiom-interval
```

## Usage

### Interval Type

The `IntervalType` class provides type transformation, comparison, and formatting for interval values:

```php
use Superscript\Axiom\Interval\Types\IntervalType;
use Superscript\Interval\Interval;
use Superscript\Interval\IntervalNotation;
use Brick\Math\BigNumber;

$type = new IntervalType();

// Transform from string
$result = $type->transform('[1,2]');
$interval = $result->unwrap()->unwrap();

// Transform from Interval object
$interval = new Interval(
    BigNumber::of(1),
    BigNumber::of(2),
    IntervalNotation::Closed
);
$result = $type->transform($interval);

// Compare two intervals
$a = $type->transform('[1,2]')->unwrap()->unwrap();
$b = $type->transform('[1,2]')->unwrap()->unwrap();
$isEqual = $type->compare($a, $b); // true

// Format interval back to string
$formatted = $type->format($interval); // "[1,2]"
```

#### Interval Notation

Intervals can be specified using standard mathematical notation:
- `[1,2]` - Closed interval (includes both endpoints)
- `(1,2)` - Open interval (excludes both endpoints)
- `[1,2)` - Half-open interval (includes left, excludes right)
- `(1,2]` - Half-open interval (excludes left, includes right)

### Operator Overloading

The `IntervalOverloader` class enables comparison operations between intervals and numeric values:

```php
use Superscript\Axiom\Interval\Operators\IntervalOverloader;
use Superscript\Interval\Interval;

$overloader = new IntervalOverloader();
$interval = Interval::fromString('[2,3]');

// Check if operator is supported
$supports = $overloader->supportsOverloading($interval, 1, '>'); // true

// Evaluate comparisons
$overloader->evaluate($interval, 1, '>');   // true (interval is greater than 1)
$overloader->evaluate($interval, 2, '>=');  // true (interval is >= 2)
$overloader->evaluate($interval, 3, '<=');  // true (interval is <= 3)
$overloader->evaluate($interval, 4, '<');   // true (interval is less than 4)
```

#### Supported Operators

- `>` - Greater than
- `>=` - Greater than or equal to
- `<` - Less than
- `<=` - Less than or equal to

## Development

### Testing

Run the full test suite:

```bash
composer test
```

Or run individual test suites:

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

### Docker Support

The project includes Docker configuration for development:

```bash
docker-compose up -d
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- Built on [superscript/interval](https://github.com/superscript/interval)
- Extends [gosuperscript/schema](https://github.com/mannum/schema)
