<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/ThrowableAssertsTrait.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot;
use Throwable;

trait ThrowableAssertsTrait
{
    /**
     * Asserts that a callable throws a specific Throwable.
     *
     * @param callable               $callable               the callable to call
     * @param string                 $throwableClassName     assert that a Throwable of the given class is thrown
     * @param Constraint|string|null $throwableMessage       assert that its message matches the given constraint
     * @param int|string|null        $throwableCode          assert that its code matches the given one
     * @param bool                   $throwableExactMatch    whether an exact match of the class name is required
     * @param string                 $throwableBaseClassName catch all Throwables of the given class
     * @param string                 $message                additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrows(
        callable $callable,
        string $throwableClassName = Throwable::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $throwableBaseClassName = Throwable::class,
        string $message = ''
    ): void {
        $constraint = new CallableThrows(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            $throwableBaseClassName
        );

        Assert::assertThat($callable, $constraint, $message);
    }

    /**
     * Returns a new instance of the CallableThrows constraint.
     *
     * @param string                 $throwableClassName     assert that a Throwable of the given class is thrown
     * @param Constraint|string|null $throwableMessage       assert that its message matches the given constraint
     * @param int|string|null        $throwableCode          assert that its code matches the given one
     * @param bool                   $throwableExactMatch    whether an exact match of the class name is required
     * @param string                 $throwableBaseClassName catch all Throwables of the given class
     *
     * @return CallableThrows
     *
     * @throws PHPUnitException
     */
    public static function callableThrows(
        string $throwableClassName = Throwable::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $throwableBaseClassName = Throwable::class
    ): CallableThrows {
        return new CallableThrows(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            $throwableBaseClassName
        );
    }

    /**
     * Asserts that a callable does not throw a specific Throwable.
     *
     * @param callable               $callable               the callable to call
     * @param string                 $throwableClassName     assert that no Throwable of the given class is thrown
     * @param Constraint|string|null $throwableMessage       catch Throwables matching the given message constraint only
     * @param int|string|null        $throwableCode          catch Throwables matching the given code only
     * @param bool                   $throwableExactMatch    whether only Throwables of the given class are caught
     * @param string                 $message                additional information about the test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrowsNot(
        callable $callable,
        string $throwableClassName = Throwable::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $message = ''
    ): void {
        $constraint = new CallableThrowsNot(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch
        );

        Assert::assertThat($callable, $constraint, $message);
    }

    /**
     * Returns a new instance of the CallableThrowsNot constraint.
     *
     * @param string                 $throwableClassName     assert that no Throwable of the given class is thrown
     * @param Constraint|string|null $throwableMessage       catch Throwables matching the given message constraint only
     * @param int|string|null        $throwableCode          catch Throwables matching the given code only
     * @param bool                   $throwableExactMatch    whether only Throwables of the given class are caught
     *
     * @return CallableThrowsNot
     *
     * @throws PHPUnitException
     */
    public static function callableThrowsNot(
        string $throwableClassName = Throwable::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false
    ): CallableThrowsNot {
        return new CallableThrowsNot(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch
        );
    }

    /**
     * Returns a new instance of CallableProxy.
     * *
     * @param callable $callable     the callable to invoke
     * @param mixed    ...$arguments the arguments to pass to the callable
     *
     * @return CallableProxy
     */
    public static function callableProxy(callable $callable, ...$arguments): CallableProxy
    {
        return new CallableProxy($callable, ...$arguments);
    }

    /**
     * Returns a new instance of CachedCallableProxy.
     * *
     * @param callable $callable     the callable to invoke
     * @param mixed    ...$arguments the arguments to pass to the callable
     *
     * @return CachedCallableProxy
     */
    public static function cachedCallableProxy(callable $callable, ...$arguments): CachedCallableProxy
    {
        return new CachedCallableProxy($callable, ...$arguments);
    }
}
