<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Unit/Constraint/CallableThrowsNotTest.php>
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
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\TestCase;

/**
 * PHPUnit unit test for the CallableThrowsNot constraint.
 *
 * @see CallableThrowsNot
 */
class CallableThrowsNotTest extends TestCase
{
    /**
     * @dataProvider dataProviderInvalidParameters
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testInvalidParameters(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraintArguments = [ $className, $message, $code, $exactMatch ];
        $this->assertCallableThrows(static function () use ($constraintArguments) {
            new CallableThrowsNot(...$constraintArguments);
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
     * @param string                 $expectedDescription
     */
    public function testSelfDescribing(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $expectedDescription
    ): void {
        $constraint = null;
        $constraintArguments = [ $className, $message, $code, $exactMatch ];

        $this->assertCallableThrowsNot(static function () use (&$constraint, $constraintArguments) {
            $constraint = new CallableThrowsNot(...$constraintArguments);
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
     * @param callable               $other
     */
    public function testEvaluate(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        $other
    ): void {
        $constraint = new CallableThrowsNot($className, $message, $code, $exactMatch);

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
     * @param callable               $other
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testEvaluateFail(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        $other,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraint = new CallableThrowsNot($className, $message, $code, $exactMatch);

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
     * @param callable               $other
     * @param string                 $expectedException
     * @param string                 $expectedExceptionMessage
     */
    public function testEvaluateRethrow(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        $other,
        string $expectedException,
        string $expectedExceptionMessage
    ): void {
        $constraint = new CallableThrowsNot($className, $message, $code, $exactMatch);

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

    public function testEvaluateNoCallable(): void
    {
        $expectedException = ExpectationFailedException::class;
        $expectedExceptionMessage = "Failed asserting that 'no callable' is a callable that "
            . "does not throw a Throwable.";

        $constraint = new CallableThrowsNot();
        $other = 'no callable';

        $this->assertCallableThrows(static function () use ($constraint, $other) {
            $constraint->evaluate($other);
        }, $expectedException, $expectedExceptionMessage);
    }

    public function testEvaluateReturnsNull(): void
    {
        $constraint = new CallableThrowsNot();
        $other = static function () {};

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other);
        });

        $this->assertNull($returnValue);
    }

    public function testEvaluateReturnsTrue(): void
    {
        $constraint = new CallableThrowsNot();
        $other = static function () {};

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other, '', true);
        });

        $this->assertTrue($returnValue);
    }

    public function testEvaluateReturnsFalse(): void
    {
        $constraint = new CallableThrowsNot();
        $other = static function () {
            throw new Exception();
        };

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($constraint, $other, &$returnValue) {
            $returnValue = $constraint->evaluate($other, '', true);
        });

        $this->assertFalse($returnValue);
    }

    public function testEvaluateReturnsFalseInvalid(): void
    {
        $constraint = new CallableThrowsNot();
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
            . "Failed asserting that " . __CLASS__ . "::{closure}() does not throw a Throwable.\n"
            . "Encountered invalid Exception without a message.";

        $constraint = new CallableThrowsNot();
        $other = static function () {
            throw new Exception();
        };
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
     * @param int                    $expectedCount
     */
    public function testCountable(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        int $expectedCount
    ): void {
        $constraint = null;
        $constraintArguments = [ $className, $message, $code, $exactMatch ];

        $this->assertCallableThrowsNot(static function () use (&$constraint, $constraintArguments) {
            $constraint = new CallableThrowsNot(...$constraintArguments);
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
