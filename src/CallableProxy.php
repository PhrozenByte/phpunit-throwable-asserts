<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/CallableProxy.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts;

use Closure;
use PHPUnit\Framework\SelfDescribing;
use ReflectionException;
use ReflectionFunction;

/**
 * A simple proxy class for callables.
 *
 * This is a helper class to invoke a callable using a given set of arguments.
 * CallableProxy's advantage over an anonymous function is the fact, that it
 * can describe itself and doesn't just end up being called `{closure}()`.
 */
class CallableProxy implements SelfDescribing
{
    /** @var callable */
    protected $callable;

    /** @var mixed[] */
    protected $arguments;

    /**
     * CallableProxy constructor.
     *
     * @param callable $callable     the callable to invoke
     * @param mixed    ...$arguments the arguments to pass to the callable
     */
    public function __construct(callable $callable, ...$arguments)
    {
        $this->callable = $callable;
        $this->arguments = $arguments;
    }

    /**
     * Invokes the callable with the given arguments.
     *
     * @return mixed
     */
    public function __invoke()
    {
        $callable = $this->callable;
        return $callable(...$this->arguments);
    }

    /**
     * Returns a human-readable string representation of the callable.
     *
     * All strings match the format `<function>()` or `<class>::<function>()`.
     * `{closure}` as function name describes a anonymous function, optionally
     * also indicating the Closure's scope as class. If a callable's function
     * name is unknown, `{callable}` is returned.
     *
     * @return string string representation of the callable
     */
    public function toString(): string
    {
        if (is_string($this->callable)) {
            return sprintf('%s()', $this->callable);
        }

        if (is_array($this->callable)) {
            $className = is_object($this->callable[0]) ? get_class($this->callable[0]) : $this->callable[0];
            return sprintf('%s::%s()', $className, $this->callable[1]);
        }

        if (is_object($this->callable)) {
            if ($this->callable instanceof Closure) {
                try {
                    $closureReflector = new ReflectionFunction($this->callable);

                    $closureName = $closureReflector->getName();
                    if (substr_compare($closureName, '\\{closure}', -10) === 0) {
                        $closureName = '{closure}';
                    }

                    if ($closureReflector->getClosureThis() !== null) {
                        $closureThisClassName = get_class($closureReflector->getClosureThis());
                        return sprintf('%s::%s()', $closureThisClassName, $closureName);
                    }

                    if ($closureReflector->getClosureScopeClass() !== null) {
                        $closureScopeClassName = $closureReflector->getClosureScopeClass()->getName();
                        return sprintf('%s::%s()', $closureScopeClassName, $closureName);
                    }

                    return sprintf('%s()', $closureName);
                } catch (ReflectionException $e) {
                    // ReflectionException is never thrown, the callable typehint ensures a valid function
                    return '{closure}()';
                }
            }

            return sprintf('%s::__invoke()', get_class($this->callable));
        }

        // fallback to '{callable}()' if all else fails
        // this is for future PHP versions implementing new ways to describe callables
        return '{callable}()';
    }
}
