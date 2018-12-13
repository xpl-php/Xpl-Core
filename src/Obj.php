<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Object utilities.
 *
 * @since 1.0
 */
abstract class Obj
{

	/**
	 * Casts $value to an object.
	 *
	 * @param mixed $value
	 *
	 * @return object
	 */
	public static function from($value)
	{
		return (object)$value;
	}

	/**
	 * Checks whether the given object is empty (has no properties).
	 *
	 * An object is considered "empty" if count($object) === 0
	 *
	 * @param object $object
	 *
	 * @return bool
	 */
	public static function isEmpty($object) : bool
	{
		return self::count($object) === 0;
	}

	/**
	 * Casts an object to an array.
	 *
	 * @param object $object
	 *
	 * @return array
	 */
	public static function toArray($object) : array
	{
		if ($object instanceof Arrayable) {
			return $object->toArray();
		}

		if ($object instanceof \Traversable) {
			return iterator_to_array($object);
		}

		assert(is_object($object), new \TypeError());

		if (method_exists($object, 'toArray')) {
			return $object->toArray();
		}

		return get_object_vars($object);
	}

	/**
	 * Returns a valid "iterable" for $object.
	 *
	 * @param object $object
	 *
	 * @return iterable
	 */
	public static function toIterable($object) : iterable
	{
		if ($object instanceof \Traversable) {
			return $object;
		}

		return self::toArray($object);
	}

	/**
	 * Hydrates an object with data.
	 *
	 * @param object $object
	 * @param array|object $data
	 *
	 * @return object
	 */
	public static function hydrate($object, $data)
	{
		if ($object instanceof Hydratable) {
			$object->hydrate($data);
		} else {

			assert(is_object($object), new \TypeError());

			foreach(Variable::toIterable($data) as $key => $value) {
				$object->{$key} = $value;
			}
		}

		return $object;
	}

	/**
	 * Returns the object's "count".
	 *
	 * @param object $object
	 *
	 * @return int
	 */
	public static function count($object) : int
	{
		if ($object instanceof \Countable) {
			return count($object);
		}

		if ($object instanceof \Traversable) {
			return iterator_count($object);
		}

		return count(self::toArray($object));
	}

	/**
	 * Creates a new instance of $class.
	 *
	 * @param string|object $class
	 * @param array $args [Optional] Constructor arguments.
	 *
	 * @return object
	 */
	public static function create($class, array $args = null)
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		if (! $args) {
			return new $class();
		}

		switch(count($args)) {
			case 1:
				return new $class($args[0]);
			case 2:
				return new $class($args[0], $args[1]);
			case 3:
				return new $class($args[0], $args[1], $args[2]);
			case 4:
				return new $class($args[0], $args[1], $args[2], $args[3]);
			default:
				return (new \ReflectionClass($class))->newInstanceArgs($args);
		}
	}

	/**
	 * Resolves any aliases and returns the real class name.
	 *
	 * @param string|object $class
	 *
	 * @return string
	 */
	public static function className($class) : string
	{
		static $cache = [];

		if (is_object($class)) {
			return get_class($class);
		}

		if (! isset($cache[$class])) {
			$cache[$class] = (new \ReflectionClass($class))->getName();
		}

		return $cache[$class];
	}

	/**
	 * Returns the class basename (class name without namespace).
	 *
	 * @param string|object $class
	 *
	 * @return string
	 */
	public static function classBasename($class) : string
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		return basename(str_replace('\\', '/', $class));
	}

	/**
	 * Returns a class namespace.
	 *
	 * If class does not have a namespace, an empty string is returned.
	 *
	 * @param string|object $class
	 *
	 * @return string
	 */
	public static function classNamespace($class) : string
	{
		if (is_object($class)) {
			$class = get_class($class);
		}

		return strstr($class, '\\', true) ?: '';
	}

	/**
	 * Returns the traits used by the given object.
	 *
	 * @param object $object
	 * @param bool   $recursive [Optional] Default = false. Whether to return traits of
	 * 									   the object's parent(s) as well.
	 *
	 * @return array
	 */
	public static function uses($object, bool $recursive = false) : array
	{
		foreach($traits = class_uses($object) ?: [] as $t) {
			$traits += self::uses($t);
		}

		if ($recursive) {
			foreach(class_parents($object) ?: [] as $class) {
				$traits += self::uses($class, true);
			}
		}

		return array_unique($traits);
	}

	/**
	 * Retrieves an object property given its path in "dot notation."
	 *
	 * @param object $object Target object.
	 * @param string $path Item path given in dot-notation (e.g. "some.nested.item")
	 * @param mixed $default [Optional] Value to return if item is not found.
	 *
	 * @return mixed Value if found, otherwise $default.
	 */
	public static function get($object, string $path, $default = null)
	{
		assert(is_object($object), new \TypeError());

		if (isset($object->$path) || strpos($path, '.') === false) {
			return $object->$path;
		}

		$obj = $object;

		foreach(explode('.', $path) as $key) {

			if (! isset($obj->$key)) {
				return $default;
			}

			$obj = $obj->$key;
		}

		return $obj;
	}

	/**
	 * Sets an object property given a path in "dot notation."
	 *
	 * @param object $object
	 * @param string $path
	 * @param mixed $value
	 *
	 * @return object
	 */
	public static function set($object, string $path, $value)
	{
		assert(is_object($object), new \TypeError());

		if (false === strpos($path, '.')) {
			$object->$path = $value;
			return $object;
		}

		$obj = $object;
		$keys = explode('.', $path);
		$property = array_pop($keys);

		foreach($keys as $key) {

			if (! isset($obj->$key)) {
				$obj->$key = new \stdClass;
			}

			$obj = $obj->$key;
		}

		$obj->$property = $value;

		return $object;
	}

	/**
	 * Checks whether an object property exists with the given path.
	 *
	 * @param object $object
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function exists($object, string $path) : bool
	{
		return self::get($object, $path, null) !== null;
	}

	/**
	 * Unsets an array element given its path in "dot notation."
	 *
	 * @param object $object
	 * @param string $path Dot-notated path.
	 *
	 * @return void
	 */
	public static function delete($object, string $path)
	{
		assert(is_object($object), new \TypeError());

		if (isset($object->$path) || false === strpos($path, '.')) {
			unset($object->$path);
			return;
		}

		$obj = $object;
		$keys = explode('.', $path);
		$property = array_pop($keys);

		foreach($keys as $key) {

			if (! isset($obj->$key)) {
				return;
			}

			$obj = $obj->$key;
		}

		if ($obj && is_object($obj)) {
			unset($obj->$property);
		}
	}

}
