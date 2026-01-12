# Contributing to Axiom Interval

Thank you for your interest in contributing to Axiom Interval! We welcome contributions from the community.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/axiom-interval.git`
3. Create a new branch: `git checkout -b my-feature-branch`
4. Make your changes
5. Run the tests to ensure everything works
6. Commit your changes: `git commit -m "Description of changes"`
7. Push to your fork: `git push origin my-feature-branch`
8. Open a Pull Request

## Development Setup

### Requirements

- PHP 8.4 or higher
- Composer
- ext-intl extension

### Installation

```bash
composer install
```

### Running Tests

Before submitting a pull request, make sure all tests pass:

```bash
# Run all tests
composer test

# Run individual test suites
composer test:types    # PHPStan static analysis
composer test:unit     # PHPUnit (requires 100% code coverage)
composer test:infection # Infection mutation testing
```

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting. Run Pint before committing:

```bash
vendor/bin/pint
```

## Pull Request Guidelines

- Write clear, descriptive commit messages
- Add tests for new features
- Maintain 100% code coverage
- Update documentation as needed
- Follow the existing code style
- Keep pull requests focused on a single feature or fix

## Reporting Issues

When reporting issues, please include:

- PHP version
- Steps to reproduce the issue
- Expected behavior
- Actual behavior
- Any relevant code samples or error messages

## Questions?

Feel free to open an issue for any questions or concerns.

## License

By contributing to Axiom Interval, you agree that your contributions will be licensed under the MIT License.
