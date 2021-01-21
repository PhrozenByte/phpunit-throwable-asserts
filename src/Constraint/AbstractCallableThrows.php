<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/src/Constraint/AbstractCallableThrows.php>
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
use PhrozenByte\PHPUnitThrowableAsserts\CallableProxy;
use SebastianBergmann\Comparator\ComparisonFailure;
use Throwable;

/**
 * Abstract base class for the `CallableThrows` and `CallableThrowsNot`
 * constraints implementing some common methods.
 */
abstract class AbstractCallableThrows extends Constraint
{
    /** @var string */
    protected $className;

    /** @var Constraint|null */
    protected $messageConstraint;

    /** @var int|string|null */
    protected $code;

    /** @var bool */
    protected $exactMatch;

    /**
     * AbstractCallableThrows constructor.
     *
     * @param string                 $className  the Throwable's class name
     * @param Constraint|string|null $message    constraint to match the Throwable's message
     * @param int|string|null        $code       value to match the Throwable's code
     * @param bool                   $exactMatch whether an exact match of the Throwable's class is required
     *
     * @throws PHPUnitException
     */
    public function __construct(string $className, $message, $code, bool $exactMatch)
    {
        $className = ltrim($className, '\\');
        if (!is_a($className, Throwable::class, true)) {
            throw InvalidArgumentException::create(1, sprintf('instance of %s', Throwable::class));
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
     * {@inheritDoc}
     */
    protected function fail(
        $other,
        $description,
        ComparisonFailure $comparisonFailure = null,
        Throwable $throwable = null
    ): void {
        $failureDescription = sprintf('Failed asserting that %s.', $this->failureDescription($other));

        $throwableFailureDescription = $this->throwableFailureDescription($throwable);
        if ($throwableFailureDescription) {
            $failureDescription .= "\n" . $throwableFailureDescription;
        }

        $additionalFailureDescription = $this->additionalFailureDescription($other);
        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if ($description) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new ExpectationFailedException(
            $failureDescription,
            $comparisonFailure
        );
    }

    /**
     * Returns additional failure description for a Throwable
     *
     * @param Throwable|null $throwable the Throwable that was thrown
     *
     * @return string the failure description
     */
    protected function throwableFailureDescription(?Throwable $throwable): string
    {
        if ($throwable === null) {
            return '';
        }

        $failureDescription = sprintf('Encountered invalid %s', get_class($throwable));

        if ($throwable->getCode() !== 0) {
            $failureDescription .= sprintf(' with code %s', $throwable->getCode());
        }

        if ($throwable->getMessage() === '') {
            $failureDescription .= (($throwable->getCode() !== 0) ? ' and' : '') . ' without a message';
        } else {
            $failureDescription .= sprintf(': %s', $throwable->getMessage());
        }

        return $failureDescription . '.';
    }

    /**
     * {@inheritDoc}
     */
    protected function failureDescription($other): string
    {
        if (!is_callable($other)) {
            return $this->exporter()->export($other) . ' is a callable that ' . $this->toString();
        }

        if (!is_object($other) || !($other instanceof CallableProxy)) {
            $other = new CallableProxy($other);
        }

        return $other->toString() . ' ' . $this->toString();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return 1 + (($this->code !== null) ? 1 : 0)
            + (($this->messageConstraint !== null) ? $this->messageConstraint->count() : 0);
    }
}
