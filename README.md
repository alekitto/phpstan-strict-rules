[![Latest Stable Version](https://poser.pugx.org/kcs/phpstan-strict-rules/v/stable)](https://packagist.org/packages/kcs/phpstan-strict-rules)
[![Total Downloads](https://poser.pugx.org/kcs/phpstan-strict-rules/downloads)](https://packagist.org/packages/kcs/phpstan-strict-rules)
[![Latest Unstable Version](https://poser.pugx.org/kcs/phpstan-strict-rules/v/unstable)](https://packagist.org/packages/kcs/phpstan-strict-rules)
[![License](https://poser.pugx.org/kcs/phpstan-strict-rules/license)](https://packagist.org/packages/kcs/phpstan-strict-rules)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kcs/phpstan-strict-rules/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kcs/phpstan-strict-rules/?branch=master)
[![Build Status](https://travis-ci.org/kcs/phpstan-strict-rules.svg?branch=master)](https://travis-ci.org/kcs/phpstan-strict-rules)
[![Coverage Status](https://coveralls.io/repos/kcs/phpstan-strict-rules/badge.svg?branch=master&service=github)](https://coveralls.io/github/kcs/phpstan-strict-rules?branch=master)

## This is a fork of the popular `thecodingmachine/phpstan-strict-rules`

To push forward the development of this package and the adoption of phpstan v2, this package has been forked, as the original
repository seems semi-abandoned.
Versioning starts from 2.0 and retains all the features of the original package. Namespace has not been changed.

Once the original project restarts, it is possible that this package would be abandoned. In case the original package is left
abandoned for a longer time, this will be a hard fork, with different namespaces and so on...

TheCodingMachine's additional rules for PHPStan
===============================================

This package contains a set of rules to be added to the [wonderful PHPStan static analyzer](https://phpstan.org/).

Those rules come from [TheCodingMachine best practices](http://bestpractices.thecodingmachine.com/).
They are more "strict" than the default PHPStan rules and some may be controversial. We use those at TheCodingMachine, have found them to help us in our daily work, and ask anyone working with us to follow them.

## Rules list

### Exception related rules

- You should not throw the "Exception" base class directly [but throw a sub-class instead](http://bestpractices.thecodingmachine.com/php/error_handling.html#subtyping-exceptions).
- You should not have empty catch statements
- When throwing an exception inside a catch block, [you should pass the catched exception as the "previous" exception](http://bestpractices.thecodingmachine.com/php/error_handling.html#wrapping-an-exception-do-not-lose-the-previous-exception)
- If you catch a `Throwable`, an `Exception` or a `RuntimeException`, you must rethrow the exception.

### Superglobal related rules

- The use of [`$_GET`, `$_POST`, `$_FILES`, `$_COOKIE`, `$_SESSION`, `$_REQUEST` is forbidden](http://bestpractices.thecodingmachine.com/php/organize_your_code.html#stop-using-superglobals-).
  You should instead use your framework's request/session object.
- Superglobal usage is still tolerated at the root scope (because it is typically used once in `index.php` to initialize
  PSR-7 request object)

### Condition related rules

- Switch statements should always check for unexpected values by [implementing a default case (and throwing an exception)](http://bestpractices.thecodingmachine.com/php/defensive_programming.html#always-check-for-unexpected-values)

### Work-in-progress

    // Never use public properties
    // Never use globals

## Installation

We assume that [PHPStan](https://phpstan.org/) is already installed in your project.

To use this extension, require it in [Composer](https://getcomposer.org/):

```bash
composer require --dev thecodingmachine/phpstan-strict-rules
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, include phpstan-strict-rules.neon in your project's PHPStan config:

```yml
includes:
    - vendor/thecodingmachine/phpstan-strict-rules/phpstan-strict-rules.neon
```
</details>
