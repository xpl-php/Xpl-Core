<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Variable utilities.
 *
 * @since 1.0
 */
abstract class Variable
{

	/**
	 * Checks whether the given value is empty.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function isEmpty($value) : bool
	{
		if (empty($value)) {
			return true;
		}

		if (is_object($value)) {
			return Obj::isEmpty($value);
		}

		return false;
	}

	/**
	 * Checks whether $value is an array or an object that implements ArrayAccess.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function isArrayAccessible($value) : bool
	{
		return $value instanceof \ArrayAccess || is_array($value);
	}

	/**
	 * Casts a value to an array.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function toArray($value) : array
	{
		if (is_object($value)) {
			return Obj::toArray($value);
		}

		return (array)$value;
	}

	/**
	 * Returns the value as an 'iterable'.
	 *
	 * @param mixed $value
	 *
	 * @return iterable
	 */
	public static function toIterable($value) : iterable
	{
		return is_iterable($value) ? $value : self::toArray($value);
	}

	/**
	 * Casts a value to boolean.
	 *
	 * Returns true for "1", "true", "on" and "yes"
	 * Returns false for "0", "false", "off", "no", and ""
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function toBool($value) : bool
	{
		switch(true) {

			case is_bool($value) :
				return $value;

			case is_null($value) :
				return false;

			case is_scalar($value) :
				return Str::toBool((string)$value);

			default :
				return ! self::isEmpty($value);
		}
	}

	/**
	 * Converts a variable to a scalar value.
	 *
	 * @param mixed $var
	 *
	 * @return null|int|float|bool|string
	 *
	 * @throws \InvalidArgumentException if var cannot be cast to a scalar
	 */
	public static function toScalar($var)
	{
		if (is_null($var)) {
			return '';
		}

		if (is_scalar($var)) {
			return $var;
		}

		if (is_object($var)) {

			if (method_exists($var, '__toString')) {
				return (string)$var;
			}

			if (isset($var->scalar) && is_scalar($var->scalar)) {
				// scalar value was cast to object using `(object)$value`
				return $var->scalar;
			}
		}

		throw new \InvalidArgumentException(sprintf(
			"Cannot cast variable of type '%s' to scalar.", self::type($var))
		);
	}

	/**
	 * Returns the value's "count".
	 *
	 * @param mixed $value
	 *
	 * @return int
	 */
	public static function count($value) : int
	{
		if (is_object($value)) {
			return Obj::count($value);
		}

		return count($value);
	}

	/**
	 * Hydrates an array or object with data.
	 *
	 * @param array|object $target
	 * @param array|object $data
	 *
	 * @throws \InvalidArgumentException if $target is not an array or object.
	 *
	 * @return array|object
	 */
	public static function hydrate($target, $data)
	{
		if (is_object($target)) {
			return Obj::hydrate($target, $data);
		}

		if (is_array($target)) {
			return Arr::hydrate($target, $data);
		}

		throw new \InvalidArgumentException(sprintf(
			"Cannot hydrate variable of type '%s'.", gettype($target)
		));
	}

	/**
	 * If $var is a Closure, evaluates and returns the return value. Otherwise, returns the original argument.
	 *
	 * @param mixed $var
	 *
	 * @return mixed
	 */
	public static function result($var)
	{
		return ($var instanceof \Closure) ? $var() : $var;
	}

	/**
	 * Returns the type name of a variable.
	 *
	 * If $var is an object, returns the class name. Otherwise, returns the same as gettype()
	 *
	 * @param mixed $var
	 *
	 * @return string
	 */
	public static function type($var) : string
	{
		return is_object($var) ? get_class($var) : gettype($var);
	}

}
