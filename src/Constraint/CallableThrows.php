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
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PhrozenByte\PHPUnitThrowableAsserts\CallableProxy;
use SebastianBergmann\Comparator\ComparisonFailure;
use Throwable;

/**
 * Constraint that asserts that a callable throws a specific Throwable.
 *
 * This constraint calls the given callable and catches any Throwable matching
 * the given base class. Any other Throwable isn't caught. It then asserts that
 * the Throwable's class, message and code match the expected, or throws a
 * ExpectationFailedException otherwise.
 *
 * The class name of the expected Throwable, a optional constraint to match the
 * Throwable's message, the optional code to assert, whether an exact match of
 * the Throwable's class is required, and the Throwable base class name are
 * passed in the constructor. The callable is the value to evaluate.
 */
class CallableThrows extends Constraint
{
    /** @var string */
    protected $className;

    /** @var Constraint|null */
    protected $messageConstraint;

    /** @var int|string|null */
    protected $code;

    /** @var bool */
    protected $exactMatch;

    /** @var string */
    protected $baseClassName;

    /**
     * CallableThrows constructor.
     *
     * @param string                $className     assert that a Throwable of the given class is thrown
     * @param Constraint|mixed|null $message       assert that the Throwable matches the given message constraint
     * @param int|string|null       $code          assert that the Throwable matches the given code
     * @param bool                  $exactMatch    whether an exact match of the Throwable class is required
     * @param string                $baseClassName catch all Throwables of the given class
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $className = Throwable::class,
        $message = null,
        $code = null,
        bool $exactMatch = false,
        string $baseClassName = Throwable::class
    ) {
        if (!is_a($baseClassName, Throwable::class, true)) {
            InvalidArgumentException::create(5, sprintf('instance of %s', Throwable::class));
        }

        $className = ltrim($className, '\\');
        if (!is_a($className, $baseClassName, true)) {
            InvalidArgumentException::create(1, sprintf('instance of %s (argument #5)', $baseClassName));
        }

        if (($message !== null) && !($message instanceof Constraint)) {
            $message = new IsEqual($message);
        }

        $this->className = $className;
        $this->messageConstraint = $message;
        $this->code = $code;
        $this->exactMatch = $exactMatch;
        $this->baseClassName = $baseClassName;
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return sprintf('throws a %s', $this->className)
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
        $throwable = null;
        $comparisonFailure = null;

        if (is_callable($other)) {
            try {
                $other();
            } catch (Throwable $throwable) {
                if (!($throwable instanceof $this->baseClassName)) {
                    throw $throwable;
                }

                if ($throwable instanceof $this->className) {
                    $success = true;

                    if ($this->exactMatch && (get_class($throwable) !== $this->className)) {
                        $success = false;
                    }

                    if ($this->messageConstraint !== null) {
                        try {
                            $this->messageConstraint->evaluate($throwable->getMessage());
                        } catch (ExpectationFailedException $messageException) {
                            $comparisonFailure = $messageException->getComparisonFailure();
                            $success = false;
                        }
                    }

                    if ($this->code !== null) {
                        if ($throwable->getCode() !== $this->code) {
                            $success = false;
                        }
                    }

                    if ($success) {
                        return $returnResult ? true : null;
                    }
                }
            }
        }

        if (!$returnResult) {
            $this->fail($other, $description, $comparisonFailure, $throwable);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null, Throwable $throwable = null): void
    {
        $failureDescription = sprintf('Failed asserting that %s.', $this->failureDescription($other));

        $throwableFailureDescription = $this->throwableFailureDescription($throwable, ($comparisonFailure === null));
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
     * @param Throwable|null $throwable      the Throwable that was thrown
     * @param bool           $includeMessage whether the failure description should include the Throwable's message
     *
     * @return string the failure description
     */
    protected function throwableFailureDescription(?Throwable $throwable, bool $includeMessage = false): string
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
        } elseif (!$includeMessage) {
            $failureDescription .= (($throwable->getCode() !== 0) ? ' and' : ' with') . ' an invalid message';
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
        return 1 + (($this->messageConstraint !== null) ? 1 : 0) + (($this->code !== null) ? 1 : 0);
    }
}
