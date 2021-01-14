<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/Constraint/ExceptionConstraint.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts;

use Error;
use Exception;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot;
use Throwable;

trait ThrowableAssertsTrait
{
    /**
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $throwableBaseClassName
     * @param string   $message
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
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
     * @param string $throwableBaseClassName
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
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrowsException(
        callable $callable,
        string $throwableClassName = Exception::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $message = ''
    ): void {
        self::assertCallableThrows(
            $callable,
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            Exception::class,
            $message
        );
    }

    /**
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
     *
     * @return CallableThrows
     *
     * @throws PHPUnitException
     */
    public static function callableThrowsException(
        string $throwableClassName = Exception::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false
    ): CallableThrows {
        return static::callableThrows(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            Exception::class
        );
    }

    /**
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrowsError(
        callable $callable,
        string $throwableClassName = Error::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $message = ''
    ): void {
        self::assertCallableThrows(
            $callable,
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            Error::class,
            $message
        );
    }

    /**
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
     *
     * @return CallableThrows
     *
     * @throws PHPUnitException
     */
    public static function callableThrowsError(
        string $throwableClassName = Error::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false
    ): CallableThrows {
        return static::callableThrows(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            Error::class
        );
    }

    /**
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $message
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
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
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
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrowsNoException(
        callable $callable,
        string $throwableClassName = Exception::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $message = ''
    ): void {
        self::assertCallableThrowsNot(
            $callable,
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            $message
        );
    }

    /**
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
     *
     * @return CallableThrowsNot
     *
     * @throws PHPUnitException
     */
    public static function callableThrowsNoException(
        string $throwableClassName = Exception::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false
    ): CallableThrowsNot {
        return static::callableThrowsNot(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch
        );
    }

    /**
     * @param callable $callable
     * @param string   $throwableClassName
     * @param null     $throwableMessage
     * @param null     $throwableCode
     * @param bool     $throwableExactMatch
     * @param string   $message
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws PHPUnitException
     * @throws Throwable
     */
    public static function assertCallableThrowsNoError(
        callable $callable,
        string $throwableClassName = Error::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false,
        string $message = ''
    ): void {
        self::assertCallableThrowsNot(
            $callable,
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch,
            $message
        );
    }

    /**
     * @param string $throwableClassName
     * @param null   $throwableMessage
     * @param null   $throwableCode
     * @param bool   $throwableExactMatch
     *
     * @return CallableThrowsNot
     *
     * @throws PHPUnitException
     */
    public static function callableThrowsNoError(
        string $throwableClassName = Error::class,
        $throwableMessage = null,
        $throwableCode = null,
        bool $throwableExactMatch = false
    ): CallableThrowsNot {
        return static::callableThrowsNot(
            $throwableClassName,
            $throwableMessage,
            $throwableCode,
            $throwableExactMatch
        );
    }
}