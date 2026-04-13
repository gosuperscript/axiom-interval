<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

/*
 * The robiningelbrecht/phpunit-coverage-tools extension reads the
 * --min-coverage=<value> argument from $_SERVER['argv']. In PHPUnit 12
 * the old "vendor/bin/phpunit -d --min-coverage=100" trick triggers a
 * fatal PHPUnit runner warning ("Failed to set '--min-coverage=100'"),
 * so we inject the argument here instead. PHPUnit loads this bootstrap
 * before bootstrapping extensions, so the coverage extension still sees
 * the argument while PHPUnit's own CLI parser does not.
 */
if (! in_array('--min-coverage=100', $_SERVER['argv'] ?? [], true)) {
    $_SERVER['argv'][] = '--min-coverage=100';
    $_SERVER['argc']   = count($_SERVER['argv']);
}
