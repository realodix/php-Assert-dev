<?php

namespace Realodix\Assert;

class Assert
{
    /**
     * Checks a parameter, that is, throws a ParameterAssertionException if $condition is false.
     * This is similar to Assert::precondition().
     *
     * @note This is intended for checking parameters in constructors and setters.
     * Checking parameters in every function call is not recommended, since it may have a
     * negative impact on performance.
     *
     * @param string $name        The name of the parameter that was checked.
     * @param string $description The message to include in the exception if the condition fails.
     *
     * @throws ParameterAssertionException if $condition is not true.
     * @psalm-assert bool $condition
     */
    public static function parameter(bool $condition, string $name, string $description): void
    {
        if (! $condition) {
            throw new ParameterAssertionException($name, $description);
        }
    }

    /**
     * Checks an parameter's type, that is, throws a InvalidArgumentException if $value is
     * not of $type. This is really a special case of Assert::precondition().
     *
     * @param string|string[] $types The parameter's expected type. Can be the name of a native type
     *                               or a class or interface, or a list of such names.
     * @param mixed           $value The parameter's actual value.
     * @param string          $name  The name of the parameter that was checked.
     *
     * @throws \InvalidArgumentException
     */
    public static function isType($types, $value, string $name, int $argument = 1): void
    {
        if (is_string($types)) {
            $types = explode('|', $types);
        }

        $stack = debug_backtrace();
        $typeGiven = implode('|', $types);

        $invalidArgument = sprintf(
            'Argument #%d of %s() must be %s %s, %s given',
            $argument,
            $stack[1]['function'],
            \in_array(lcfirst($typeGiven)[0], ['a', 'e', 'i', 'o', 'u'], true) ? 'an' : 'a',
            $typeGiven,
            get_debug_type($value) // symfony/polyfill-php80
        );

        if (! self::hasType($value, $types)) {
            throw new \InvalidArgumentException($invalidArgument);
        }
    }

    /**
     * @param mixed    $value
     * @param string[] $allowedTypes
     */
    private static function hasType($value, array $allowedTypes): bool
    {
        // Apply strtolower because gettype returns "NULL" for null values.
        $type = strtolower(gettype($value));

        if (in_array($type, $allowedTypes)) {
            return true;
        }

        if ((is_object($value) && self::isInstanceOf($value, $allowedTypes))
            || (in_array('callable', $allowedTypes) && is_callable($value))) {
            return true;
        }

        if ((in_array('int', $allowedTypes) && is_integer($value))
            || (in_array('float', $allowedTypes) && is_float($value))) {
            return true;
        }

        if ((in_array('bool', $allowedTypes) && is_bool($value))
            || (in_array('false', $allowedTypes) && $value === false)
            || (in_array('true', $allowedTypes) && $value === true)) {
            return true;
        }

        if (in_array('traversable', $allowedTypes) && is_array($value) && $value instanceof \Traversable) {
            return true;
        }

        return false;
    }

    /**
     * @param object   $value
     * @param string[] $allowedTypes
     */
    private static function isInstanceOf($value, array $allowedTypes): bool
    {
        foreach ($allowedTypes as $type) {
            if ($value instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
