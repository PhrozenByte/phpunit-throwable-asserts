<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/CachedCallableProxy.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts;

/**
 * A simple proxy class for callables with return value caching.
 *
 * @see CallableProxy
 */
class CachedCallableProxy extends CallableProxy
{
    /** @var mixed */
    protected $returnValue;

    /**
     * {@inheritDoc}
     */
    public function __invoke()
    {
        $this->returnValue = parent::__invoke();
        return $this->returnValue;
    }

    /**
     * Returns the cached return value of the callable from its last invocation.
     *
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }
}
