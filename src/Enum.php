<?php

declare(strict_types=1);

namespace Xpl;

use ReflectionClass;
use OutOfBoundsException;
use UnexpectedValueException;

/**
 * Enum class based on SplEnum
 *
 * @since 1.0
 */
abstract class Enum
{

	use Traits\PropertyAccessReadable;

	/**
	 * Default value used if none is provided in the constructor.
	 *
	 * @var mixed
	 */
	const __default = null;

	/**
	 * The enum instance value.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Constructor.
	 *
	 * @throws \UnexpectedValueException if given an invalid value.
	 *
	 * @param mixed $value Initial value. Defaults to class __default constant.
	 */
	final public function __construct($value = null)
	{
		if (! func_num_args()) {
			$value = static::__default;
		} else if (! $this->isValidValue($value)) {
			throw new UnexpectedValueException("Invalid enum value");
		}

		$this->value = $value;
	}

	/**
	 * Returns the instance value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns an array of constants defined by the calling class.
	 *
	 * @param  bool $include_default [Optional] Default = false
	 *
	 * @return array
	 */
    final public static function getConstList(bool $include_default = false) : array
	{
		static $constCache;

	    if (! isset($constCache)) {
            $constCache = (new ReflectionClass(static::class))->getConstants();
        }

		$constants = $constCache;

		if (! $include_default) {
			unset($constants['__default']);
		}

	    return $constants;
    }

	/**
	 * Returns a list of the enum constant names.
	 *
	 * @param  bool $include_default [Optional] Default = false
	 *
	 * @return array
	 */
	final public static function names(bool $include_default = false) : array
	{
		return array_keys(static::getConstList($include_default));
	}

	/**
	 * Returns a list of the enum constant values.
	 *
	 * @param  bool $include_default [Optional] Default = false
	 *
	 * @return array
	 */
	final public static function values(bool $include_default = false) : array
	{
		return array_values(static::getConstList($include_default));
	}

	/**
	 * Checks whether the given name is a valid constant for the called class.
	 *
	 * @param  string $name
	 * @param  bool $strict [Optional] Default = false
	 *
	 * @return bool
	 */
    final public static function isValidName(string $name, bool $strict = false) : bool
	{
        $constants = static::getConstList();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        return in_array(strtolower($name), array_map('strtolower', array_keys($constants)));
    }

	/**
	 * Checks whether the given value is a valid (constant) value for the called class.
	 *
	 * @param  mixed $value
	 *
	 * @return bool
	 */
    final public static function isValidValue($value) : bool
	{
		return in_array($value, static::getConstList(true), true);
    }

	/**
	 * Creates an Enum instance of the called class from a constant name.
	 *
	 * @throws \OutOfBoundsException if $name is not a valid constant.
	 *
	 * @param  string $name
	 * @param  bool $strict [Optional] Default = false
	 *
	 * @return Enum
	 */
	final public static function fromName(string $name, bool $strict = false) : Enum
	{
		$constants = static::getConstList(true);
		$constName = null;

		if (array_key_exists($name, $constants)) {
			$constName = $name;
		} else if (! $strict) {
			$lcname = strtolower($name);
			foreach($constants as $_name => $_value) {
				if (strtolower($_name) === $lcname) {
					$constName = $_name;
					break;
				}
			}
		}

		if (is_null($constName)) {
			throw new OutOfBoundsException(sprintf(
				"Invalid enum constant: '%s'%s.", $name, ($strict ? ' (strict)' : '')
			));
		}

		return new static($constants[$constName]);
	}
}
