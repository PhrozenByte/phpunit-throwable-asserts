PHPUnitThrowableAssertions
==========================

[`PHPUnitThrowableAssertions`](https://github.com/PhrozenByte/phpunit-throwable-asserts) is a small [PHPUnit](https://phpunit.de/) extension to assert that callables do or do not throw a specific Exception, Error, or Throwable.

This PHPUnit extension allows developers to test whether callables throw exceptions, errors and other throwables in a single assertion using the more intuitive "assert that" approach. It's a replacement for PHPUnit's built-in `expectException()`, `expectExceptionMessage()` and `expectExceptionCode()` methods - just more powerful.

You want more PHPUnit constraints? Check out [`PHPUnitArrayAssertions`](https://github.com/PhrozenByte/phpunit-array-asserts)! It introduces various assertions to test PHP arrays and array-like data in a single assertion. The PHPUnit extension is often used for API testing to assert whether an API result matches certain criteria - regarding both its structure, and the data.

Made with :heart: by [Daniel Rudolf](https://www.daniel-rudolf.de). `PHPUnitThrowableAssertions` is free and open source software, released under the terms of the [MIT license](https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/LICENSE).

Install
-------

`PHPUnitThrowableAssertions` is available on [Packagist.org](https://packagist.org/packages/phrozenbyte/phpunit-throwable-asserts) and can be installed using [Composer](https://getcomposer.org/):

```shell
composer require --dev phrozenbyte/phpunit-throwable-asserts
```

This PHPUnit extension was initially written for PHPUnit 8, but should work fine with any later PHPUnit version. If it doesn't, please don't hesitate to open a [new Issue on GitHub](https://github.com/PhrozenByte/phpunit-throwable-asserts/issues/new), or, even better, create a Pull Request with a proposed fix.
