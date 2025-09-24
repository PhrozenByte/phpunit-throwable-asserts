<?php
/**
 * PHPUnitThrowableAssertions - Throwable-related PHPUnit assertions.
 *
 * @copyright Copyright (c) 2021, Daniel Rudolf (<https://www.daniel-rudolf.de>)
 *
 * This file is copyrighted by the contributors recorded in the version control
 * history of the file, available from the following original location:
 *
 * <https://github.com/PhrozenByte/phpunit-throwable-asserts/blob/master/tests/TestCase.php>
 *
 * @license http://opensource.org/licenses/MIT The MIT License
 *
 * SPDX-License-Identifier: MIT
 * License-Filename: LICENSE
 */

declare(strict_types=1);

namespace PhrozenByte\PHPUnitThrowableAsserts\Tests;

use Closure;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrows;
use PhrozenByte\PHPUnitThrowableAsserts\Constraint\CallableThrowsNot;
use ReflectionObject;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var array[][][] */
    protected static $testDataSets = [];

    /**
     * Asserts that a callable throws a specific Throwable.
     *
     * This is a very basic implementation of the `CallableThrows` constraint,
     * implemented with PHPUnit core features only. It's way less powerful,
     * but works for testing `PHPUnitThrowableAsserts`.
     *
     * @see CallableThrows
     *
     * @param callable    $callable           the Callable to call
     * @param string      $throwableClassName assert that a Throwable of the given class is thrown
     * @param string|null $throwableMessage   assert that its message matches the given string
     *
     * @throws AssertionFailedError
     * @throws Throwable
     */
    public function assertCallableThrows(
        callable $callable,
        string $throwableClassName = Throwable::class,
        ?string $throwableMessage = null
    ): void {
        $throwableFailure = '';
        $throwableComparisonFailure = null;

        try {
            $callable();
        } catch (Throwable $throwable) {
            try {
                $this->assertInstanceOf($throwableClassName, $throwable);

                if ($throwableMessage !== null) {
                    $this->assertSame($throwableMessage, $throwable->getMessage());
                }

                // assertion is true
                return;
            } catch (ExpectationFailedException $e) {
                // proceed with failure
                $throwableComparisonFailure = $e->getComparisonFailure();
            }

            $throwableFailure = sprintf('Encountered invalid %s', get_class($throwable));
            $throwableFailure .= $throwable->getMessage() ? sprintf(': %s.', $throwable->getMessage()) : '.';
        }

        $callableName = $this->getCallableName($callable);
        $failure = sprintf('Failed asserting that %s throws a %s', $callableName, $throwableClassName);
        $failure .= $throwableMessage ? sprintf(" whose message is equal to '%s'.", $throwableMessage) : '.';
        $failure .= $throwableFailure ? "\n" . $throwableFailure : '';
        $failure .= $throwableComparisonFailure ? "\n" . $throwableComparisonFailure->getDiff() : '';

        $this->fail($failure);
    }

    /**
     * Asserts that a callable does not throw a specific Throwable.
     *
     * This is a very basic implementation of the `CallableThrowsNot`
     * constraint, implemented with PHPUnit core features only. It's way
     * less powerful, but works for testing `PHPUnitThrowableAsserts`.
     *
     * @see CallableThrowsNot
     *
     * @param callable    $callable           the Callable to call
     * @param string      $throwableClassName assert that no Throwable of the given class is thrown
     * @param string|null $throwableMessage   catch Throwables matching the given message only
     *
     * @throws AssertionFailedError
     * @throws Throwable
     */
    public function assertCallableThrowsNot(
        callable $callable,
        string $throwableClassName = Throwable::class,
        ?string $throwableMessage = null
    ): void {
        try {
            $callable();
        } catch (Throwable $throwable) {
            try {
                $this->assertInstanceOf($throwableClassName, $throwable);

                if ($throwableMessage !== null) {
                    $this->assertSame($throwableMessage, $throwable->getMessage());
                }
            } catch (ExpectationFailedException $e) {
                // re-throw non-matching Throwable
                throw $throwable;
            }

            $throwableFailure = sprintf('Encountered invalid %s', get_class($throwable));
            $throwableFailure .= $throwable->getMessage() ? sprintf(': %s.', $throwable->getMessage()) : '.';

            $callableName = $this->getCallableName($callable);
            $failure = sprintf('Failed asserting that %s throws a %s', $callableName, $throwableClassName);
            $failure .= $throwableMessage ? sprintf(" whose message is equal to '%s'.", $throwableMessage) : '.';
            $failure .= $throwableFailure ? "\n" . $throwableFailure : '';

            $this->fail($failure);
        }

        // noop assertion, otherwise we wouldn't have any assertion
        $this->assertTrue(true);
    }

    /**
     * Returns a human-readable string representation of a Callable.
     *
     * This is a very basic implementation of the `CallableProxy::toString()`
     * method. It e.g. doesn't incorporates a Closure's scope, but works for
     * testing `PHPUnitThrowableAsserts`.
     *
     * @psalm-suppress RedundantCondition
     *
     * @param callable $callable the Callable to describe
     *
     * @return string string representation of the Callable
     */
    private function getCallableName(callable $callable): string
    {
        if (is_string($callable)) {
            return sprintf('%s()', $callable);
        } elseif (is_array($callable)) {
            $className = is_object($callable[0]) ? get_class($callable[0]) : $callable[0];
            return sprintf('%s::%s()', $className, $callable[1]);
        } elseif (is_object($callable)) {
            return ($callable instanceof Closure) ? '{closure}()' : sprintf('%s::__invoke()', get_class($callable));
        }

        return '{callable}()';
    }

    /**
     * Returns test data sets for a particular test. The test data sets are
     * stored in YAML files matching the test's class name.
     *
     * @param string $testName name of the test
     *
     * @return array[] test data sets
     */
    protected function getTestDataSets(string $testName): array
    {
        $error = null;
        $testClassName = (new ReflectionObject($this))->getShortName();
        $testDatasetsFile = __DIR__ . '/data/' . $testClassName . '.yml';

        if (!isset(self::$testDataSets[$testClassName])) {
            if (!file_exists($testDatasetsFile)) {
                $error = 'No such file or directory';
            } elseif (!is_file($testDatasetsFile)) {
                $error = 'Not a file';
            } elseif (!is_readable($testDatasetsFile)) {
                $error = 'Permission denied';
            } else {
                try {
                    self::$testDataSets[$testClassName] = $this->parseYaml(file_get_contents($testDatasetsFile));
                } catch (YamlParseException $e) {
                    $error = sprintf('YAML parse error: %s', $e->getMessage());
                }
            }
        }

        if (!isset(self::$testDataSets[$testClassName][$testName])) {
            if ($error === null) {
                $error = sprintf('Dataset "%s" not found', $testName);
            }
        }

        if ($error !== null) {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            throw new PHPUnitException(sprintf(
                'Test data file "%s" for %s::%s() (%s data sets) is invalid: %s',
                $testDatasetsFile,
                $stack[1]['class'],
                $stack[1]['function'],
                $testClassName,
                $error
            ));
        }

        return self::$testDataSets[$testClassName][$testName];
    }

    /**
     * Parses a YAML string.
     *
     * @param string $input YAML string
     *
     * @return mixed parsed data
     *
     * @throws YamlParseException
     */
    private function parseYaml(string $input)
    {
        $yaml = Yaml::parse($input);

        if (isset($yaml['~anchors'])) {
            unset($yaml['~anchors']);
        }

        $parseRecursive = static function ($value) use (&$parseRecursive) {
            if (is_array($value)) {
                if (isset($value['<<<'])) {
                    $mergeValues = $value['<<<'];
                    unset($value['<<<']);

                    if (is_array($mergeValues) && $mergeValues) {
                        if (!isset($mergeValues[0]) || ($mergeValues !== array_values($mergeValues))) {
                            $mergeValues = [ $mergeValues ];
                        }

                        foreach ($mergeValues as $mergeValue) {
                            $value = array_replace_recursive($value, $mergeValue);
                        }
                    }
                }

                if (isset($value['~closureReturn'])) {
                    $returnValue = $parseRecursive($value['~closureReturn']);
                    return static function () use ($returnValue) {
                        return $returnValue;
                    };
                }

                if (isset($value['~closureThrow'])) {
                    $throwValue = $parseRecursive($value['~closureThrow']);
                    return static function () use ($throwValue) {
                        throw $throwValue;
                    };
                }

                if (isset($value['~object'])) {
                    $className = $value['~object'];
                    unset($value['~object']);

                    $parameters = array_values(array_map($parseRecursive, $value));
                    return new $className(...$parameters);
                }

                return array_map($parseRecursive, $value);
            }

            return $value;
        };

        return $parseRecursive($yaml);
    }
}
