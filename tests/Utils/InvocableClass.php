<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/Utils/InvocableClass.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts\Tests\Utils;

use Throwable;

/**
 * InvocableClass is a simple implementation of a invocable class.
 */
class InvocableClass
{
    /** @var string|null */
    protected $throwableClassName;

    /** @var array */
    protected $throwableArguments = [];

    /**
     * InvocableClass constructor.
     *
     * @param string|null $throwableClassName    the Throwable's class name to thrown when invoked
     * @param mixed       ...$throwableArguments the arguments to pass to the Throwable
     */
    public function __construct(?string $throwableClassName = null, ...$throwableArguments)
    {
        $this->throwableClassName = $throwableClassName;
        $this->throwableArguments = $throwableArguments;
    }

    /**
     * Invokes the class and either returns all arguments passed or throws.
     *
     * @param mixed ...$arguments the values to return
     *
     * @return array list of the arguments passed
     *
     * @throws Throwable the expected Throwable
     */
    public function __invoke(...$arguments): array
    {
        if ($this->throwableClassName !== null) {
            /** @psalm-var class-string<Throwable> $className */
            $className = $this->throwableClassName;
            throw new $className(...$this->throwableArguments);
        }

        return $arguments;
    }

    /**
     * Empty declaration of a method.
     */
    public function otherMethod(): void
    {
        // do nothing
    }

    /**
     * Empty declaration of a static method.
     */
    public static function otherStaticMethod(): void
    {
        // do nothing
    }
}
