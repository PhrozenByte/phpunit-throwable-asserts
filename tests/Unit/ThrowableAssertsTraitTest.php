<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Unit/ThrowableAssertsTraitTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts\Tests\Unit;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\SelfDescribing;
use PhrozenByte\PHPUnitThrowableAsserts\Assert;
use PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy;
use PhrozenByte\PHPUnitThrowableAsserts\CallableProxy;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait;
use Throwable;

/**
 * PHPUnit unit test for the ThrowableAsserts trait using the Assert class.
 *
 * This unit test uses Mockery instance mocking. This is affected by other unit
 * tests and will affect other unit tests. Thus we run all tests in separate
 * processes and without preserving the global state.
 *
 * @see ThrowableAssertsTrait
 * @see Assert
 *
 * @covers \PhrozenByte\PHPUnitThrowableAsserts\ThrowableAssertsTrait
 * @covers \PhrozenByte\PHPUnitThrowableAsserts\Assert
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ThrowableAssertsTraitTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @dataProvider dataProviderCallableThrows
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     */
    public function testCallableThrows(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName
    ): void {
        $this->mockConstraintInstance(
            CallableThrows::class,
            [ $className, $message, $code, $exactMatch, $baseClassName ]
        );

        $constraint = Assert::callableThrows($className, $message, $code, $exactMatch, $baseClassName);
        $this->assertInstanceOf(CallableThrows::class, $constraint);
    }

    /**
     * @dataProvider dataProviderCallableThrows
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     * @param string                 $baseClassName
     * @param array                  $callableExceptionData
     */
    public function testAssertCallableThrows(
        string $className,
        $message,
        $code,
        bool $exactMatch,
        string $baseClassName,
        array $callableExceptionData
    ): void {
        $callable = static function () use ($callableExceptionData) {
            /** @psalm-var class-string<Throwable> $className */
            $className = array_shift($callableExceptionData);
            throw new $className(...$callableExceptionData);
        };

        $this->mockConstraintInstance(
            CallableThrows::class,
            [ $className, $message, $code, $exactMatch, $baseClassName ],
            [ $callable, '' ]
        );

        Assert::assertCallableThrows($callable, $className, $message, $code, $exactMatch, $baseClassName);
    }

    /**
     * @return array[]
     */
    public function dataProviderCallableThrows(): array
    {
        return [
            [
                Exception::class,
                'Something went wrong',
                null,
                false,
                Throwable::class,
                [ Exception::class, 'Something went wrong' ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCallableThrowsNot
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     */
    public function testCallableThrowsNot(
        string $className,
        $message,
        $code,
        bool $exactMatch
    ): void {
        $this->mockConstraintInstance(
            CallableThrowsNot::class,
            [ $className, $message, $code, $exactMatch ]
        );

        $constraint = Assert::callableThrowsNot($className, $message, $code, $exactMatch);
        $this->assertInstanceOf(CallableThrowsNot::class, $constraint);
    }

    /**
     * @dataProvider dataProviderCallableThrowsNot
     *
     * @param string                 $className
     * @param Constraint|string|null $message
     * @param int|string|null        $code
     * @param bool                   $exactMatch
     */
    public function testAssertCallableThrowsNot(
        string $className,
        $message,
        $code,
        bool $exactMatch
    ): void {
        $callable = static function () {};

        $this->mockConstraintInstance(
            CallableThrowsNot::class,
            [ $className, $message, $code, $exactMatch ],
            [ $callable, '' ]
        );

        Assert::assertCallableThrowsNot($callable, $className, $message, $code, $exactMatch);
    }

    /**
     * @return array[]
     */
    public function dataProviderCallableThrowsNot(): array
    {
        return [
            [
                Exception::class,
                'Something went wrong',
                null,
                false,
            ],
        ];
    }

    public function testCallableProxy(): void
    {
        $callable = static function () {};
        $arguments = [ 1, 2, 3 ];

        $this->mockCallableProxyInstance(
            CallableProxy::class,
            array_merge([ $callable ], $arguments)
        );

        Assert::callableProxy($callable, ...$arguments);
    }

    public function testCachedCallableProxy(): void
    {
        $callable = static function () {};
        $arguments = [ 1, 2, 3 ];

        $this->mockCallableProxyInstance(
            CachedCallableProxy::class,
            array_merge([ $callable ], $arguments)
        );

        Assert::cachedCallableProxy($callable, ...$arguments);
    }

    /**
     * Mocks a constraint class using Mockery instance mocking.
     *
     * @param string     $className
     * @param array      $constructorArguments
     * @param array|null $evaluateArguments
     *
     * @return MockInterface
     */
    private function mockConstraintInstance(
        string $className,
        array $constructorArguments = [],
        ?array $evaluateArguments = null
    ): MockInterface {
        $instanceMock = Mockery::mock('overload:' . $className, Constraint::class);

        $instanceMock->shouldReceive('__construct')
            ->with(...$constructorArguments)
            ->once();

        if ($evaluateArguments !== null) {
            $instanceMock->shouldReceive('evaluate')
                ->with(...$evaluateArguments)
                ->atMost()->once();
        } else {
            $instanceMock->shouldNotReceive('evaluate');
        }

        $instanceMock->shouldReceive([
            'count'    => 1,
            'toString' => 'is tested'
        ]);

        return $instanceMock;
    }

    /**
     * Mocks the CallableProxy classes using Mockery instance mocking.
     *
     * @param string     $className
     * @param array      $constructorArguments
     *
     * @return MockInterface
     */
    private function mockCallableProxyInstance(
        string $className,
        array $constructorArguments = []
    ): MockInterface {
        $instanceMock = Mockery::mock('overload:' . $className, SelfDescribing::class);

        $instanceMock->shouldReceive('__construct')
            ->with(...$constructorArguments)
            ->once();

        $instanceMock->shouldReceive('__invoke')
            ->withNoArgs()
            ->atMost()->once();

        $instanceMock->shouldReceive([
            'toString' => 'SomeClass::someMethod()'
        ]);

        return $instanceMock;
    }
}
