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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use Throwable;

/**
 * Constraint that asserts that a callable doesn't throw a specific Throwable.
 *
 * This constraint calls the given callable and catches any Throwable matching
 * the given class, message and code. All conditions must match, otherwise the
 * Throwable is re-thrown.
 *
 * This is NOT the same as negating the CallableThrows constraint, because this
 * constraint will re-throw any non-matching Throwable instead of throwing a
 * ExpectationFailedException. A ExpectationFailedException is only thrown when
 * the callable throws a Throwable matching all conditions.
 *
 * The class name of the expected Throwable, a optional constraint to match the
 * Throwable's message, the optional code to assert, and whether an exact match
 * of the Throwable's class is required are passed in the constructor. The
 * callable is the value to evaluate.
 */
class CallableThrowsNot extends Constraint
{
    use CallableThrowsTrait;

    /** @var string */
    protected $className;

    /** @var Constraint|null */
    protected $messageConstraint;

    /** @var int|string|null */
    protected $code;

    /** @var bool */
    protected $exactMatch;

    /**
     * CallableThrowsNot constructor.
     *
     * @param string                $className  assert that no Throwable of the given class is thrown
     * @param Constraint|mixed|null $message    catch Throwables with a message matching the given constraint only
     * @param int|string|null       $code       catch Throwables with the given code only
     * @param bool                  $exactMatch whether an exact match of the Throwable class is caught only
     *
     * @throws PHPUnitException
     */
    public function __construct(
        string $className = Throwable::class,
        $message = null,
        $code = null,
        bool $exactMatch = false
    ) {
        $className = ltrim($className, '\\');
        if (!is_a($className, Throwable::class, true)) {
            InvalidArgumentException::create(1, sprintf('instance of %s', Throwable::class));
        }

        if (($message !== null) && !($message instanceof Constraint)) {
            $message = new IsEqual($message);
        }

        $this->className = $className;
        $this->messageConstraint = $message;
        $this->code = $code;
        $this->exactMatch = $exactMatch;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return sprintf('does not throw a %s', $this->className)
            . ($this->exactMatch ? ' (exact match)' : '')
            . (($this->code !== null) ? sprintf(' with code %s', $this->code) : '')
            . (($this->messageConstraint && ($this->code !== null)) ? ' and' : '')
            . ($this->messageConstraint ? ' whose message ' . $this->messageConstraint->toString() : '');
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate($other, string $description = '', bool $returnResult = false)
    {
        if (!is_callable($other)) {
            if (!$returnResult) {
                $this->fail($other, $description);
            }

            return false;
        }

        try {
            $other();
        } catch (Throwable $throwable) {
            if (!($throwable instanceof $this->className)) {
                throw $throwable;
            }

            if ($this->exactMatch && (get_class($throwable) !== $this->className)) {
                throw $throwable;
            }

            if ($this->messageConstraint !== null) {
                try {
                    $this->messageConstraint->evaluate($throwable->getMessage());
                } catch (ExpectationFailedException $messageException) {
                    throw $throwable;
                }
            }

            if ($this->code !== null) {
                if ($throwable->getCode() !== $this->code) {
                    throw $throwable;
                }
            }

            if (!$returnResult) {
                $this->fail($other, $description);
            }

            return false;
        }

        return $returnResult ? true : null;
    }

    /**
     * {@inheritDoc}
     */
    protected function failureDescription($other): string
    {
        if (!is_callable($other)) {
            return $this->exporter()->export($other) . ' is a callable that ' . $this->toString();
        }

        return $this->describeCallable($other) . ' ' . $this->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return 1 + (($this->messageConstraint !== null) ? 1 : 0) + (($this->code !== null) ? 1 : 0);
    }
}