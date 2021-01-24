<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Unit/Constraint/CallableThrowsTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts\Tests\Unit\Constraint;

use Exception;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\TestCase;

/**
 * PHPUnit unit test for the CallableThrows constraint.
 *
 * @see CallableThrows
 */
class CallableThrowsTest extends TestCase
{
    /**
     * @dataProvider dataProviderInvalidParameters
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testInvalidParameters(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraintArguments = [ $className, $message, $code, $exactMatch, $baseClassName ];
        $this->assertCallableThrows(static function () use ($constraintArguments) {
            new CallableThrows(...$constraintArguments);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @return array[]
     */
    public function dataProviderInvalidParameters(): array
    {
        return $this->getTestDataSets('testInvalidParameters');
    }

    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param string                 $expectedDescription
     */
    public function testSelfDescribing(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        string $expectedDescription
    ): void {
        $constraint = null;
        $constraintArguments = [ $className, $message, $code, $exactMatch, $baseClassName ];

        $this->assertCallableThrowsNot(static function () use (&$constraint, $constraintArguments) {
            $constraint = new CallableThrows(...$constraintArguments);
        });

        $this->assertSame($expectedDescription, $constraint->toString());
    }

    /**
     * @return array
     */
    public function dataProviderSelfDescribing(): array
    {
        return $this->getTestDataSets('testSelfDescribing');
    }

    /**
     * @dataProvider dataProviderEvaluate
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param callable               $other
     */
    public function testEvaluate(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        $other
    ): void {
        $constraint = new CallableThrows($className, $message, $code, $exactMatch, $baseClassName);

        $this->assertCallableThrowsNot(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        });
    }

    /**
     * @return array
     */
    public function dataProviderEvaluate(): array
    {
        return $this->getTestDataSets('testEvaluate');
    }

    /**
     * @dataProvider dataProviderEvaluateFail
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param callable               $other
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testEvaluateFail(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        $other,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraint = new CallableThrows($className, $message, $code, $exactMatch, $baseClassName);

        $this->assertCallableThrows(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @return array
     */
    public function dataProviderEvaluateFail(): array
    {
        return $this->getTestDataSets('testEvaluateFail');
    }

    /**
     * @dataProvider dataProviderEvaluateRethrow
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param callable               $other
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testEvaluateRethrow(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        $other,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraint = new CallableThrows($className, $message, $code, $exactMatch, $baseClassName);

        $this->assertCallableThrows(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @return array
     */
    public function dataProviderEvaluateRethrow(): array
    {
        return $this->getTestDataSets('testEvaluateRethrow');
    }

    public function testEvaluateNoThrow(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = 'Failed asserting that ' . __CLASS__ . '::{closure}() throws a Throwable.';

        $constraint = new CallableThrows();
        $other = function () {};

        $this->assertCallableThrows(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        }, $expectedException, $expectedExceptionMessage);
    }

    public function testEvaluateNoCallable(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = "Failed asserting that 'no callable' is a callable that throws a Throwable.";

        $constraint = new CallableThrows();
        $other = 'no callable';

        $this->assertCallableThrows(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        }, $expectedException, $expectedExceptionMessage);
    }

    public function testEvaluateReturnsNull(): void
    {
        $constraint = new CallableThrows();
        $other = static function () {
            throw new Exception();
        };

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other);
        });

        $this->assertNull($returnValue);
    }

    public function testEvaluateReturnsTrue(): void
    {
        $constraint = new CallableThrows();
        $other = static function () {
            throw new Exception();
        };

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other, '', true);
        });

        $this->assertTrue($returnValue);
    }

    public function testEvaluateReturnsFalse(): void
    {
        $constraint = new CallableThrows();
        $other = static function () {};

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other, '', true);
        });

        $this->assertFalse($returnValue);
    }

    public function testEvaluateReturnsFalseInvalid(): void
    {
        $constraint = new CallableThrows();
        $other = 'no callable';

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other, '', true);
        });

        $this->assertFalse($returnValue);
    }

    public function testEvaluateCustomMessage(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = "This is a unit test.\n"
            . "Failed asserting that " . __CLASS__ . "::{closure}() throws a Throwable.";

        $constraint = new CallableThrows();
        $other = static function () {};
        $message = 'This is a unit test.';

        $this->assertCallableThrows(static function () use ($constraint, $other, $message) {
            $constraint->evaluate($other, $message);
        }, $expectedException, $expectedExceptionMessage);
    }

    /**
     * @dataProvider dataProviderCountable
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param int                    $expectedCount
     */
    public function testCountable(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        int $expectedCount
    ): void {
        $constraint = null;
        $constraintArguments = [ $className, $message, $code, $exactMatch, $baseClassName ];

        $this->assertCallableThrowsNot(static function () use (&$constraint, $constraintArguments) {
            $constraint = new CallableThrows(...$constraintArguments);
        });

        $this->assertSame($expectedCount, $constraint->count());
    }

    /**
     * @return array[]
     */
    public function dataProviderCountable(): array
    {
        return $this->getTestDataSets('testCountable');
    }
}
