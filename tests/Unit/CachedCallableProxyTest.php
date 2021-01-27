<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Unit/CachedCallableProxyTest.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts\Tests\Unit;

use Closure;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\Utils\InvocableClass;

/**
 * PHPUnit unit test for the CachedCallableProxy helper class.
 *
 * @see CachedCallableProxy
 *
 * @covers \PhrozenByte\PHPUnitThrowableAsserts\CachedCallableProxy
 */
class CachedCallableProxyTest extends TestCase
{
    /**
     * @dataProvider dataProviderSelfDescribing
     *
     * @param callable $callable
     * @param array    $arguments
     * @param string   $expectedDescription
     */
    public function testSelfDescribing(
        callable $callable,
        array $arguments,
        string $expectedDescription
    ): void {
        $callableProxy = new CachedCallableProxy($callable, ...$arguments);
        $this->assertSame($expectedDescription, $callableProxy->toString());
    }

    /**
     * @return array
     */
    public function dataProviderSelfDescribing(): array
    {
        return [
            [ 'count', [], 'count()' ],
            [ [ Closure::class, 'bind' ], [], 'Closure::bind()' ],
            [ [ new Exception(), 'getMessage' ], [], 'Exception::getMessage()' ],
            [ [ InvocableClass::class, 'otherStaticMethod' ], [], InvocableClass::class . '::otherStaticMethod()' ],
            [ [ new InvocableClass(), 'otherMethod' ], [], InvocableClass::class . '::otherMethod()' ],
            [ function () {}, [], __CLASS__ . '::{closure}()' ],
            [ static function () {}, [], __CLASS__ . '::{closure}()' ],
            [ Closure::bind(function () {}, null, null ), [], '{closure}()' ],
            [ Closure::bind(function () {}, new Exception(), null), [], 'Exception::{closure}()' ],
            [ Closure::bind(function () {}, new Exception(), InvocableClass::class), [], 'Exception::{closure}()' ],
            [ Closure::bind(function () {}, null, InvocableClass::class), [], InvocableClass::class . '::{closure}()' ],
            [ new InvocableClass(), [], InvocableClass::class . '::__invoke()' ],
        ];
    }

    public function testInvocation(): void
    {
        /** @var InvocableClass|MockObject $callable */
        $callable = $this->createTestProxy(InvocableClass::class);
        $callable->expects($this->once())
            ->method('__invoke');
        $arguments = [ 1, 2, 3 ];

        $callableProxy = new CachedCallableProxy($callable, ...$arguments);
        $this->assertSame($arguments, $callableProxy());
    }

    public function testGetReturnValue(): void
    {
        /** @var InvocableClass|MockObject $callable */
        $callable = $this->createTestProxy(InvocableClass::class);
        $callable->expects($this->once())
            ->method('__invoke');
        $arguments = [ 1, 2, 3 ];

        $callableProxy = new CachedCallableProxy($callable, ...$arguments);

        $returnValue = null;
        $this->assertCallableThrowsNot(static function () use ($callableProxy, &$returnValue) {
            $returnValue = $callableProxy();
        });

        $this->assertSame($arguments, $returnValue);
        $this->assertSame($arguments, $callableProxy->getReturnValue());
        $this->assertNull($callableProxy->getThrowable());
    }

    public function testGetThrowable(): void
    {
        /** @var InvocableClass|MockObject $callable */
        $callable = $this->createTestProxy(InvocableClass::class, [ Exception::class ]);
        $callable->expects($this->once())
            ->method('__invoke');
        $arguments = [ 1, 2, 3 ];

        $callableProxy = new CachedCallableProxy($callable, ...$arguments);

        $returnValue = null;
        $this->assertCallableThrows(static function () use ($callableProxy, &$returnValue) {
            $returnValue = $callableProxy();
        }, Exception::class);

        $this->assertNull($returnValue);
        $this->assertNull($callableProxy->getReturnValue());
        $this->assertInstanceOf(Exception::class, $callableProxy->getThrowable());
    }
}
