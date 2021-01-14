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

namespace PhrozenByte\PHPUnitThrowableAsserts\Constraint;

use Closure;
use ReflectionException;
use ReflectionFunction;

/**
 * Common methods for the CallableThrows and CallableThrowsNot constraints.
 */
trait CallableThrowsTrait
{
    /**
     * Returns a human-readable string representation of a callable.
     *
     * All strings match the format `<function>()` or `<class>::<function>()`.
     * `{closure}` as function name describes a anonymous function, optionally
     * also indicating the Closure's scope as class. If a callable's function
     * name is unknown, `{callable}` is returned.
     *
     * @param callable $callable the callable to describe
     *
     * @return string string representation of the callable
     */
    protected function describeCallable(callable $callable): string
    {
        if (is_string($callable)) {
            return sprintf('%s()', $callable);
        }

        if (is_array($callable)) {
            $className = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            return sprintf('%s::%s()', $className, $callable[1]);
        }

        if (is_object($callable)) {
            if ($callable instanceof Closure) {
                try {
                    $closureReflector = new ReflectionFunction($callable);

                    $closureName = $closureReflector->getName();
                    if (substr_compare($closureName, '\\{closure}', -10) === 0) {
                        $closureName = '{closure}';
                    }

                    if ($closureReflector->getClosureScopeClass() !== null) {
                        $closureScopeClassName = $closureReflector->getClosureScopeClass()->getName();
                        return sprintf('%s::%s()', $closureScopeClassName, $closureName);
                    }

                    return sprintf('%s()', $closureName);
                } catch (ReflectionException $e) {
                    return '{closure}()';
                }
            }

            return sprintf('%s::__invoke()', get_class($callable));
        }

        return '{callable}()';
    }
}
