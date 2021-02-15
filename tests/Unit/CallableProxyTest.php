<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Unit/CallableProxyTest.php>
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
use PhrozenByte\PHPUnitThrowableAsserts\CallableProxy;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\TestCase;
use PhrozenByte\PHPUnitThrowableAsserts\Tests\Utils\InvocableClass;

/**
 * PHPUnit unit test for the CallableProxy helper class.
 *
 * @see CallableProxy
 *
 * @covers \PhrozenByte\PHPUnitThrowableAsserts\CallableProxy
 */
class CallableProxyTest extends TestCase
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
        $callableProxy = new CallableProxy($callable, ...$arguments);
        $this->assertSame($expectedDescription, $callableProxy->toString());
    }

    /**
     * @psalm-suppress NullArgument
     *
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
            [ new CallableProxy('count'), [], 'count()' ],
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

        $callableProxy = new CallableProxy($callable, ...$arguments);
        $this->assertSame($arguments, $callableProxy());
    }
}
