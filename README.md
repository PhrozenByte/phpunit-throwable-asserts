PHPUnitThrowableAssertions
==========================

[![MIT license](https://raw.githubusercontent.com/PhrozenByte/phpunit-throwable-asserts/master/.github/license.svg)](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/LICENSE)
[![Code coverage](https://raw.githubusercontent.com/PhrozenByte/phpunit-throwable-asserts/master/.github/coverage.svg)](https://github.com/PhrozenByte/phpunit-throwable-asserts)

[`PHPUnitThrowableAssertions`](https://github.com/PhrozenByte/phpunit-throwable-asserts) is a small [PHPUnit](https://phpunit.de/) extension to assert that Callables do or do not throw a specific Exception, Error, or Throwable.

This PHPUnit extension allows developers to test whether Callables throw Exceptions, Errors and other Throwables in a single assertion using the more intuitive "assert that" approach. It's a replacement for PHPUnit's built-in `expectException()`, `expectExceptionMessage()` and `expectExceptionCode()` methods - just more powerful.

You want more PHPUnit constraints? Check out [`PHPUnitArrayAssertions`](https://github.com/PhrozenByte/phpunit-array-asserts)! It introduces various assertions to test PHP arrays and array-like data in a single assertion. The PHPUnit extension is often used for API testing to assert whether an API result matches certain criteria - regarding both its structure, and the data.

Made with :heart: by [Daniel Rudolf](https://www.daniel-rudolf.de). `PHPUnitThrowableAssertions` is free and open source software, released under the terms of the [MIT license](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/LICENSE).

**Table of contents:**

1. [Install](#install)
2. [Usage](#usage)
    1. [Constraint `CallableThrows`](#constraint-callablethrows)
    2. [Constraint `CallableThrowsNot`](#constraint-callablethrowsnot)
    3. [`CallableProxy` and `CachedCallableProxy`](#callableproxy-and-cachedcallableproxy)
    4. [PHP errors, warnings and notices](#php-errors-warnings-and-notices)

Install
-------

`PHPUnitThrowableAssertions` is available on [Packagist.org](https://packagist.org/packages/phrozenbyte/phpunit-throwable-asserts) and can be installed using [Composer](https://getcomposer.org/):

```shell
composer require --dev phrozenbyte/phpunit-throwable-asserts
```

This PHPUnit extension was initially written for PHPUnit 8, but should work fine with any later PHPUnit version. If it doesn't, please don't hesitate to open a [new Issue on GitHub](https://github.com/PhrozenByte/phpunit-throwable-asserts/issues/new), or, even better, create a Pull Request with a proposed fix.

Usage
-----

There are three (equivalent) options to use `PHPUnitThrowableAssertions`:

- By using the static [class `PhrozenByte\PHPUnitThrowableAssertions\Assert`](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/Assert.php)
- By using the [trait `PhrozenByte\PHPUnitThrowableAssertions\ThrowableAssertsTrait`](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/ThrowableAssertsTrait.php) in your test case
- By creating new [constraint instances](https://github.com/PhrozenByte/phpunit-throwable-asserts/tree/master/src/Constraint) (`PhrozenByte\PHPUnitThrowableAssertions\Constraint\…`)

All options do exactly the same. Creating new constraint instances is useful for advanced assertions, e.g. together with `PHPUnit\Framework\Constraint\LogicalAnd`.

If you want to pass arguments to your Callable, you might want to use [`CallableProxy`](#callableproxy-and-cachedcallableproxy). If you want to access the Callable's return value or a possibly thrown Throwable, use `CachedCallableProxy` instead (specifically its `getReturnValue()` and `getThrowable()` methods). Using `CallableProxy` vastly improves error handling.

As explained above, `PHPUnitThrowableAssertions` is a more powerful alternative to PHPUnit's built-in `expectException()`. However, please note that PHPUnit's built-in `expectExceptionMessage()` matches sub strings (i.e. `$this->expectExceptionMessage('test')` doesn't just match the message `"test"`, but also `"This is a test"`), while `PHPUnitThrowableAssertions` checks for equality by default (i.e. `$message = 'test'` matches the message `"test"` only). However, `PHPUnitThrowableAssertions` allows you to not just use strings, but also arbitrary constraints. So, for example, to achieve sub string matching, pass an instance of the `PHPUnit\Framework\Constraint\StringContains` constraint instead (i.e. `$message = $this->stringContains('test')` also matches the message `"This is a test"`).

### Constraint `CallableThrows`

The [`CallableThrows` constraint](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/Constraint/CallableThrows.php) asserts that a Callable throws a specific `Throwable`.

This constraint calls the given Callable (parameter `$callable`) and catches any `Throwable` matching the given base class (parameter `$throwableBaseClassName`, defaults to `Throwable`). Any other `Throwable` isn't caught. It then asserts that the `Throwable`'s class (optional parameter `$throwableClassName`, defaults to `Throwable`), message (optional parameter `$throwableMessage`, defaults to `null`) and code (optional parameter `$throwableCode`, defaults to `null`) match the expected, or throws a  `ExpectationFailedException` otherwise. The exception message can either be a string, requiring an exact match, or an arbitrary `Constraint` (e.g. `PHPUnit\Framework\Constraint\StringContains`) to match the exception message. The constraint optionally requires an exact match of the class name (optional parameter `$throwableExactMatch`, defaults to `false`).

The `ThrowableAssertsTrait` trait exposes two public methods for the `CallableThrows` constraint: Use `ThrowableAssertsTrait::assertCallableThrows()` to perform an assertion, and `ThrowableAssertsTrait::callableThrows()` to create a new instance of the `CallableThrows` constraint.

**Usage:**

```php
// using `PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait` trait
ThrowableAssertsTrait::assertCallableThrows(
    callable $callable,                                // the Callable to call
    string $throwableClassName = Throwable::class,     // assert that a Throwable of the given class is thrown
    Constraint|string $throwableMessage = null,        // assert that its message matches the given constraint
    int|string $throwableCode = null,                  // assert that its code matches the given one
    bool $throwableExactMatch = false,                 // whether an exact match of the class name is required
    string $throwableBaseClassName = Throwable::class, // catch all Throwables of the given class
    string $message = ''                               // additional information about the test
);

// using new instance of `PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows`
new CallableThrows(
    string $className = Throwable::class,
    Constraint|string $message = null,
    int|string $code = null,
    bool $exactMatch = false,
    string $baseClassName = Throwable::class
);
```

**Example:**

```php
$controller = new BookController();
$bookName = "The Hitchhiker's Guide to the Galaxy";
$bookReleaseDate = '1979-10-12';

$this->assertCallableThrows(
    $this->callableProxy([ $controller, 'create' ], $bookName, $bookReleaseDate),
    BookAlreadyExistsException::class,
    'Unable to create book: Book already exists'
);
```

**Debugging:**

```php
$service = new HitchhikersGuideService();
$towel = false;
$answer = 42;

$this->assertCallableThrows(
    static function () use ($service, $towel, $answer) {
        $service->checkAnswer($answer); // throws a OpaqueAnswerException
        $service->checkTowel($towel);   // throws a PanicException (unreachable code)
    },
    PanicException::class,
    'I forgot my towel'
);

// Will fail with the following message:
//
//     Failed asserting that {closure}() throws a PanicException whose message is 'Time to panic'.
//     Encountered invalid OpaqueAnswerException: I do not understand.
//     --- Expected
//     +++ Actual
//     @@ @@
//     -'Time to panic'
//     +'I do not understand'
```

### Constraint `CallableThrowsNot`

The [`CallableThrowsNot` constraint](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/Constraint/CallableThrowsNot.php) asserts that a Callable doesn't throw a specific `Throwable`. It can be used as a more specific alternative to PHPUnit's built-in `expectNotToPerformAssertions()` method.

This constraint calls the given Callable (parameter `$callable`) and catches any `Throwable` matching the given class (optional parameter `$throwableClassName`, defaults to `Throwable`), message (optional parameter `$throwableMessage`, defaults to `null`) and code (optional parameter `$throwableCode`, defaults to `null`). All conditions must match, otherwise the `Throwable` is re-thrown. The exception message can either be a string, requiring an exact match, or an arbitrary `Constraint` (e.g. `PHPUnit\Framework\Constraint\StringContains`) to match the exception message. The constraint optionally requires an exact match of the class name (optional parameter `$throwableExactMatch`, defaults to `false`).

This is *not* the same as negating the `CallableThrows` constraint, which consumes all non-matching `Throwable`s and throws a `ExpectationFailedException` instead. `CallableThrowsNot` will rather re-throw any non-matching `Throwable`. A `ExpectationFailedException` is only thrown when the Callable throws a `Throwable` matching all given conditions.

The `ThrowableAssertsTrait` trait exposes two public methods for the `CallableThrowsNot` constraint: Use `ThrowableAssertsTrait::assertCallableThrowsNot()` to perform an assertion, and `ThrowableAssertsTrait::callableThrowsNot()` to create a new instance of the `CallableThrowsNot` constraint.

**Usage:**

```php
// using `PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait` trait
ThrowableAssertsTrait::assertCallableThrowsNot(
    callable $callable,                            // the Callable to call
    string $throwableClassName = Throwable::class, // assert that no Throwable of the given class is thrown
    Constraint|string $throwableMessage = null,    // catch Throwables matching the given message constraint only
    int|string $throwableCode = null,              // catch Throwables matching the given code only
    bool $throwableExactMatch = false,             // whether only Throwables of the given class are caught
    string $message = ''                           // additional information about the test
);

// using new instance of `PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot`
new CallableThrowsNot(
    string $className = Throwable::class,
    Constraint|string $message = null,
    int|string $code = null,
    bool $exactMatch = false
);
```

**Example:**

```php
$controller = CharacterController();
$character = 'Prostetnik Vogon Jeltz';

$this->assertCallableThrowsNot(
    $this->callableProxy([ $controller, 'meet' ], $character),
    VogonWantsToReadPoetException::class
);
```

**Debugging:**

```php
$controller = new BookController();
$bookName = "The Hitchhiker's Guide to the Galaxy";
$bookReleaseDate = '1979-10-12';

$this->assertCallableThrowsNot(
    $this->callableProxy([ $controller, 'create' ], $bookName, $bookReleaseDate),
    BookAlreadyExistsException::class
);

// Will fail with the following message:
//
//     Failed asserting that BookController::create() does not throw a BookAlreadyExistsException
//     Encountered invalid BookAlreadyExistsException: Unable to create book: Book already exists
```

### `CallableProxy` and `CachedCallableProxy`

`PHPUnitThrowableAsserts` invokes Callables without arguments and discards a possible return value due to how PHPUnit evaluates values. One solution for this is to use anonymous functions with variable inheritance. As a neat alternative, `PHPUnitThrowableAsserts` provides the [`CallableProxy`](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/CallableProxy.php) and [`CachedCallableProxy`](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/CachedCallableProxy.php) helper classes.

Both helper classes receive the Callable to invoke (argument `$callable`), and the arguments to pass (any following argument, variadic `$arguments`) in their constructor. They furthermore implement PHPUnit's `PHPUnit\Framework\SelfDescribing` interface and the `toString()` method, improving error handling by allowing `PHPUnitThrowableAsserts` to better designate the called method. `CachedCallableProxy` additionally implements the `getReturnValue()` and `getThrowable()` methods. `getReturnValue()` returns the cached return value of the Callables last invocation, while `getThrowable()` returns a possibly thrown `Throwable`.

The `ThrowableAssertsTrait` trait exposes two public methods to create instances of `CallableProxy` and `CachedCallableProxy`: Use `ThrowableAssertsTrait::callableProxy()` to create a new instance of `CallableProxy`, or `ThrowableAssertsTrait::cachedCallableProxy()` to create a new instance of `CachedCallableProxy`.

**Usage:**

```php
// create new instance of `PhrozenByte\PHPUnitThrowableAsserts\CallableProxy`
// using the `PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait` trait
ThrowableAssertsTrait::callableProxy(
     callable $callable,    // the Callable to invoke
     mixed    ...$arguments // the arguments to pass to the Callable
);

// create new instance of `PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy`
// using the `PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait` trait
$proxy = ThrowableAssertsTrait::cachedCallableProxy(
     callable $callable,    // the Callable to invoke
     mixed    ...$arguments // the arguments to pass to the Callable
);

// get return value of the Callable (`CachedCallableProxy` only)
$proxy->getReturnValue();

// get a possibly thrown Throwable (`CachedCallableProxy` only)
$proxy->getThrowable();
```

**Example:**

```php
$computer = new DeepThought();
$question = 'What is the Answer to the Ultimate Question of Life, the Universe, and Everything?';

// using `PhrozenByte\PHPUnitThrowableAsserts\CallableProxy`
// if the assertion fails, `ExpectationFailedException`'s message will point to DeepThought::ask() as source
$askQuestion = $this->cachedCallableProxy([ $computer, 'ask' ], $question);
$this->assertCallableThrowsNot($askQuestion);
$answer = $askQuestion->getReturnValue();

// using anonymous function
// if the assertion fails, `ExpectationFailedException` will just name {closure} as source
$answer = null;
$this->assertCallableThrowsNot(static function () use ($computer, $question, &$answer) {
    // use variable reference to pass the return value
    $answer = $computer->ask($question);
});
```

### PHP errors, warnings and notices

PHPUnit converts [PHP errors](https://www.php.net/manual/en/function.error-reporting.php) (`E_RECOVERABLE_ERROR`), warnings (`E_WARNING` and `E_USER_WARNING`), notices (`E_NOTICE`, `E_USER_NOTICE` and `E_STRICT`), and deprecation notices (`E_DEPRECATED` and `E_USER_DEPRECATED`) to `PHPUnit\Framework\Error\…` exceptions (`…\Error`, `…\Warning`, `…\Notice` and `…\Deprecated` respectively) by default. This allows you to use `PHPUnitThrowableAssertions`'s [`assertCallableThrows()`](#constraint-callablethrows) and [`assertCallableThrowsNot()`](#constraint-callablethrowsnot) assertions to also catch any PHP error; simply use one of the `PHPUnit\Framework\Error\…` classes.

Please don't confuse PHP errors with PHP's [`Error` class](https://www.php.net/manual/de/class.error.php) introduced in PHP 7.0. The latter already is a `Throwable` and can be caught as usual.

**Example:**

```php
$this->assertCallableThrows(
    static function () {
        // triggers a E_NOTICE PHP error
        echo $undefinedVariable;
    },
    \PHPUnit\Framework\Error\Notice::class,
    'Undefined variable: undefinedVariable'
);
```
