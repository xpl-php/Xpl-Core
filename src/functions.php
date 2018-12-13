<?php

declare(strict_types=1);

namespace Xpl;

use TypeError;

const NULL_STRING = "";

/**
 * Casts a value to an array.
 *
 * @param mixed $var
 *
 * @return array
 */
function to_array($var) : array
{
	if (\is_object($var)) {

		if ($var instanceof Arrayable) {
			return $var->toArray();
		}

		if ($var instanceof \Traversable) {
			return \iterator_to_array($var);
		}

		if (\method_exists($var, 'toArray')) {
			return $var->toArray();
		}

		return \get_object_vars($var);
	}

	return (array)$var;
}

/**
 * Returns the value as an 'iterable'.
 *
 * @param mixed $var
 *
 * @return iterable
 */
function to_iterable($var) : iterable
{
	return \is_iterable($var) ? $var : to_array($var);
}

/**
 * Casts a value to boolean.
 *
 * @param mixed $var
 *
 * @return bool
 */
function to_bool($var) : bool
{
	switch($var) {
		case false:
		case null:
		case 0:
		case NULL_STRING:
			return false;
		case true:
		case 1:
			return true;
	}

	return filter_var($var, FILTER_VALIDATE_BOOLEAN);
}

/**
 * Hydrates an array or object with data.
 *
 * @param array|object $target
 * @param array|object $data
 *
 * @throws TypeError if $target is not an array or object.
 *
 * @return array|object
 */
function hydrate($target, $data)
{
	if ($target instanceof Hydratable) {

		$target->hydrate($data);

	} else if (\is_array($target)) {

		$target = \array_merge($target, to_array($data));

	} else if (\is_object($target)) {

		foreach(to_iterable($data) as $key => $value) {
			$target->{$key} = $value;
		}

	} else {
		throw new TypeError(sprintf(
			"Expecting array or object, given: %s", vartype($target)
		));
	}

	return $target;
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
function vartype($var) : string
{
	return \is_object($var) ? \get_class($var) : \gettype($var);
}

/**
 * Checks whether $var is an array or an object that implements ArrayAccess.
 *
 * @param mixed $var
 *
 * @return bool
 */
function is_array_like($var) : bool
{
	return $var instanceof \ArrayAccess || \is_array($var);
}

/**
 * Checks whether $var can be cast to a string.
 *
 * @param  mixed $var
 *
 * @return bool True if $var is NULL, scalar, or an object with __toString method.
 */
function is_stringable($var) : bool
{
	return \is_scalar($var)
		|| \is_null($var)
		|| (\is_object($var) && \method_exists($var, '__toString'));
}



interface Requirement
{
	public function isSatisfied() : bool;
	public function getErrorMessage() : string;
}

abstract class MinimumVersionRequirement implements Requirement
{

	abstract public function getSystemVersion() : string;

	abstract public function getRequiredMinimumVersion() : string;

	public function isSatisfied() : bool
	{
		return version_compare($this->getSystemVersion(), $this->getRequiredMinimumVersion()) >= 0;
	}

	public function getErrorMessage() : string
	{
		return 'System does not meet minimum version requirement.';
	}

}

class MinimumPHPVersionRequirement extends MinimumVersionRequirement
{

	private $min_version;

	public function __construct(string $version)
	{
		$this->min_version = $version;
	}

	public function getSystemVersion() : string
	{
		return PHP_VERSION;
	}

	public function getRequiredMinimumVersion() : string
	{
		return $this->min_version;
	}

	public function getErrorMessage() : string
	{
		return 'System does not meet minimum PHP version requirement.'
			. PHP_EOL
			. sprintf(
				'Requires: %s; Current: %s',
				$this->getRequiredMinimumVersion(),
				$this->getSystemVersion()
			);
	}

}

class ExtensionRequirement extends MinimumVersionRequirement
{

	private $extension;
	private $min_version;

	public function __construct(string $extension, string $min_version = null)
	{
		$this->extension = $extension;
		$this->min_version = $min_version;
	}

	public function isSatisfied() : bool
	{
		if (! extension_loaded($this->extension)) {
			return false;
		}

		return empty($this->min_version) ? true : parent::isSatisfied();
	}

	public function getSystemVersion() : string
	{
		return phpversion($this->extension);
	}

	public function getRequiredMinimumVersion() : string
	{
		return $this->min_version;
	}

	public function getErrorMessage() : string
	{
		return 'System does not meet extension requirement.'
			. PHP_EOL
			. sprintf(
				'Requires: %s %s; Current: %s',
				$this->extension,
				$this->getRequiredMinimumVersion(),
				($this->getSystemVersion() || 'Not installed')
			);
	}

}

class Requirements extends \SplObjectStorage
{

	public function __construct(iterable $objects = null)
	{
		if ($objects) {
			foreach($objects as $obj) {
				$this->attach($obj);
			}
		}
	}

	public function __invoke()
	{
		foreach($this as $requirement) {
			if (! $requirement->isSatisfied()) {
				throw new ErrorException($requirement->getErrorMessage());
			}
		}
	}
}

$requires = new Requirements([
	new MinimumVersionRequirement('7.1.2'),
	new ExtensionRequirement('http')
]);

$requires();
