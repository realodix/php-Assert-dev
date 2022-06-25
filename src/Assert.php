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
     * @param  bool  $condition
     * @param  string  $name The name of the parameter that was checked.
     * @param  string  $description The message to include in the exception if the condition fails.
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
     * @note If possible, type hints should be used instead of calling this function.
     * It is intended for cases where type hints to not work, e.g. for checking union types.
     *
     * @param  string|string[]  $types The parameter's expected type. Can be the name of a native type
     *                          or a class or interface, or a list of such names. For compatibility with
     *                          versions before 0.4.0, multiple types can also be given separated by
     *                          pipe characters ("|").
     * @param  mixed  $value The parameter's actual value.
     * @param  string  $name The name of the parameter that was checked.
     *
     * @throws ParameterTypeException if $value is not of type (or, for objects, is not an
     *                                instance of) $type.
     */
    public static function parameterType($types, $value, $name): void
    {
        if (is_string($types)) {
            $types = explode('|', $types);
        }
        if (! self::hasType($value, $types)) {
            throw new ParameterTypeException($name, implode('|', $types));
        }
    }

    /**
     * @param  string  $type Either "integer" or "string". Mixing "integer|string" is not supported
     *                       because this is PHP's default anyway. It is of no value to check this.
     * @param  array  $value The parameter's actual value. If this is not an array, a
     *                       ParameterTypeException is raised.
     * @param  string  $name The name of the parameter that was checked.
     *
     * @throws ParameterTypeException if one of the keys in the array $value is not of type $type.
     */
    public static function parameterKeyType(string $type, array $value, $name): void
    {
        self::parameterType('array', $value, $name);

        if ($type !== 'integer' && $type !== 'string') {
            throw new ParameterAssertionException('type', 'must be "integer" or "string"');
        }

        foreach ($value as $key => $element) {
            if (gettype($key) !== $type) {
                throw new ParameterKeyTypeException($name, $type);
            }
        }
    }

    /**
     * Checks the type of all elements of an parameter, assuming the parameter is an array,
     * that is, throws a ParameterElementTypeException if any elements in $value are not of $type.
     *
     * @param  string|string[]  $types The elements' expected type. Can be the name of a native type
     *                                 or a class or interface. Multiple types can be given in an array
     *                                 (or a string separated by a pipe character ("|"), for compatibility
     *                                 with versions before 0.5.0).
     * @param  array  $value The parameter's actual value. If this is not an array, a ParameterTypeException
     *                       is raised.
     * @param  string  $name The name of the parameter that was checked.
     *
     * @throws ParameterTypeException        If $value is not an array.
     * @throws ParameterElementTypeException If an element of $value  is not of type
     *                                       (or, for objects, is not an instance of) $type.
     */
    public static function parameterElementType($types, array $value, string $name): void
    {
        self::parameterType('array', $value, $name);
        if (is_string($types)) {
            $types = explode('|', $types);
        }

        foreach ($value as $element) {
            if (! self::hasType($element, $types)) {
                throw new ParameterElementTypeException($name, implode('|', $types));
            }
        }
    }

    /**
     * @param  mixed  $value
     * @param  string[]  $allowedTypes
     * @return bool
     */
    private static function hasType($value, array $allowedTypes): bool
    {
        // Apply strtolower because gettype returns "NULL" for null values.
        $type = strtolower(gettype($value));

        if (in_array($type, $allowedTypes)) {
            return true;
        }

        if (in_array('callable', $allowedTypes) && is_callable($value)) {
            return true;
        }

        if (is_object($value) && self::isInstanceOf($value, $allowedTypes)) {
            return true;
        }

        if (is_array($value) && in_array('Traversable', $allowedTypes)) {
            return true;
        }

        if ($value === false && in_array('false', $allowedTypes)) {
            return true;
        }
        if ($value === true && in_array('true', $allowedTypes)) {
            return true;
        }

        return false;
    }

    /**
     * @param  object  $value
     * @param  string[]  $allowedTypes
     * @return bool
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
