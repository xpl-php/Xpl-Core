<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Array utilities
 *
 * @since 1.0
 */
abstract class Arr
{

	/**
	 * Casts $value to an array.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function from($value) : array
	{
		if (is_object($value)) {
			return Obj::toArray($value);
		}

		return (array)$value;
	}

	/**
	 * Hydrates an array with data.
	 *
	 * @param array $array
	 * @param array|object $data
	 *
	 * @return array
	 */
	public static function hydrate(array $array, $data) : array
	{
		return array_merge($array, Variable::toArray($data));
	}

	/**
	 * Checks whether the array is a zero-based integer indexed array.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function isIndexed(array $array) : bool
	{
		return ! $array || array_keys($array) === range(0, count($array) - 1);
	}

	/**
	 * Checks whether the array is an associative array.
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
	public static function isAssociative(array $array) : bool
	{
		return $array && array_keys($array) !== range(0, count($array) - 1);
	}

	/**
	 * Get the first n elements.
	 *
	 * @param array $array
	 * @param int $number [Optional]
	 *
	 * @return mixed|array
	 */
	public static function first(array $array, int $number = null)
	{
		if ($number) {
			return array_slice($array, 0, $number);
		}

		return $array ? reset($array) : null;
	}

	/**
	 * Get the last n elements.
	 *
	 * @param array $array
	 * @param int $number [Optional]
	 *
	 * @return mixed|array
	 */
	public static function last(array $array, int $number = null)
	{
		if ($number) {
			return array_slice($array, -$number);
		}

		return $array ? end($array) : null;
	}

	/**
	 * Exclude the last n elements.
	 *
	 * @param array $array
	 * @param int $number [Optional] Default = 1
	 *
	 * @return array
	 */
	public static function initial(array $array, int $number = 1) : array
	{
		return array_slice($array, 0, count($array) - $number);
	}

	/**
	 * Get the rest of the elements.
	 *
	 * @param array $array
	 * @param int $offset [Optional] Default = 1
	 *
	 * @return array
	 */
	public static function rest(array $array, int $offset = 1) : array
	{
		return array_slice($array, $offset);
	}

	/**
	 * Remove duplicated values.
	 *
	 * @param array $array
	 * @param callable|null $filter [Optional]
	 *
	 * @return array
	 */
	public static function unique(array $array, callable $filter = null) : array
	{
		if ($filter) {
			$array = array_filter($array, $filter);
		} else {
			$array = array_unique($array);
		}

		return array_values($array);
	}

	/**
	 * Extract an array of values associated with $key from $array.
	 *
	 * @param array $array
	 * @param mixed $column
	 * @param mixed $index_key [Optional]
	 *
	 * @return array
	 */
	public static function column(array $array, $column, $index_key = null) : array
	{
		return array_column($array, $column, $index_key);
	}

	/**
	 * Merges a vector of arrays.
	 *
	 * More performant than using array_merge in a loop.
	 *
	 * @author facebook/libphutil
	 *
	 * @param array $arrays Array of arrays to merge.
	 *
	 * @return array Merged arrays.
	 */
	public static function mergev(array $arrays) : array
	{
		return empty($arrays) ? [] : call_user_func_array('array_merge', $arrays);
	}

	/**
	 * Applies the callback to the elements of the given array.
	 *
	 * @param array $array
	 * @param callable $callback
	 *
	 * @return array
	 */
	public static function map(array $array, callable $callback) : array
	{
		return array_map($callback, $array);
	}

	/**
	 * Applies a callback function to each key in an array.
	 *
	 * @example
	 * $array = array('first' => 1, 'second' => 2, 'third' => 3);
	 *
	 * $newArray = Arr::mapKeys('ucfirst', $array);
	 *
	 * // array('First' => 1, 'Second' => 2, 'Third' => 3);
	 *
	 * @param array 	$array Associative array.
	 * @param callable 	$callback Callback to apply to each array key.
	 *
	 * @return array A new array with the callback applied to each key.
	 */
	public static function mapKeys(array $array, callable $callback) : array
	{
		return array_combine(array_map($callback, array_keys($array)), array_values($array));
	}

	/**
	 * Filters elements of an array using a callback function.
	 *
	 * @param array 	$array
	 * @param callable 	$callback [Optional]
	 *
	 * @return array
	 */
	public static function filter(array $array, callable $callback = null) : array
	{
		return array_filter($array, $callback);
	}

	/**
	 * Filters an array by key.
	 *
	 * Like array_filter(), except operates on keys rather than values.
	 *
	 * @example
	 *
	 * $a = array(
	 * 		0 => 0,
	 * 		1 => 1,
	 * 		"2" => 2,
	 * 		"Three" => 3
	 * );
	 *
	 * $a2 = Arr::filterKeys($a, 'is_numeric');
	 * $a3 = Arr::filterKeys($a, 'is_numeric', true);
	 *
	 * // $a2 = array(0 => 0, 1 => 1, "2" => 2);
	 * // $a3 = array("Three" => 3)
	 *
	 * @param array 		$array Array to filter by key.
	 * @param callable|null $callback Callback filter. Default null (removes empty keys).
	 * @param bool 			$negate Whether to negate the callback result. Default false.
	 *
	 * @return array Key/value pairs of $input having the filtered keys.
	 */
	public static function filterKeys(array $array, callable $callback = null, bool $negate = false) : array
	{
		$filtered = array_filter(array_keys($array), $callback);

		if ($negate) {
			return empty($filtered) ? $array : array_diff_key($array, array_flip($filtered));
		}

		return empty($filtered) ? [] : array_intersect_key($array, array_flip($filtered));
	}

	/**
	 * Remove all instances of $ignore found in $array (strict comparison).
	 *
	 * @param array $array
	 * @param array $ignore
	 *
	 * @return array
	 */
	public static function without(array $array, array $ignore) : array
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (! in_array($value, $ignore, true)) {
				$results[$key] = $value;
			}
		}

		return $results;
	}

	/**
	 * Remove all instances of $keys found in $array (strict comparison).
	 *
	 * @param array $array
	 * @param array $ignore
	 *
	 * @return array
	 */
	public static function withoutKeys(array $array, array $keys) : array
	{
		$results = [];

		foreach ($array as $key => $value) {
			if (! in_array($key, $keys, true)) {
				$results[$key] = $value;
			}
		}

		return $results;
	}

	/**
	 * Checks whether every array element passes the test.
	 *
	 * @param array 	$array Array.
	 * @param callable 	$callback Callback that returns true or false.
	 *
	 * @return bool True if all values passed the test, otherwise false.
	 */
	public static function all(array $array, callable $callback) : bool
	{
		foreach($array as $key => $value) {
			if (! $callback($value)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the index offset of $key in $array or false if the key is not found.
	 *
	 * @param array $array
	 * @param string|int $key
	 *
	 * @return int|bool
	 */
	public static function keyIndex(array $array, $key)
	{
		return array_search($key, array_keys($array), true);
	}

	/**
	 * Retrieves a array element given its path in "dot notation."
	 *
	 * @param array $array Associative array.
	 * @param string $path Item path given in dot-notation (e.g. "some.nested.item")
	 * @param mixed $default [Optional] Value to return if item is not found.
	 *
	 * @return mixed Value if found, otherwise $default.
	 */
	public static function get(array $array, string $path, $default = null)
	{
		if (isset($array[$path])) {
			return $array[$path];
		}

		if (false === strpos($path, '.')) {
			return $default;
		}

		foreach(explode('.', $path) as $key) {

			if (! array_key_exists($key, $array)) {
				return $default;
			}

			$array = $array[$key];
		}

		return $array;
	}

	/**
	 * Sets an array element given a path in "dot notation."
	 *
	 * @param array &$array
	 * @param string $path
	 * @param mixed $value
	 *
	 * @return array
	 */
	public static function set(array &$array, string $path, $value) : array
	{
		if (false === strpos($path, '.')) {
			$array[$path] = $value;
			return $array;
		}

		$a =& $array;

		foreach(explode('.', $path) as $key) {

			if (! isset($a[$key])) {
				$a[$key] = [];
			}

			$a =& $a[$key];
		}

		$a = $value;

		return $array;
	}

	/**
	 * Checks whether an array item exists with the given path.
	 *
	 * @param array &$array
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function exists(array &$array, string $path) : bool
	{
		if (false === strpos($path, '.')) {
			return array_key_exists($path, $array);
		}

		$a =& $array;

		foreach(explode('.', $path) as $key) {

			if (! array_key_exists($key, $a)) {
				return false;
			}

			$a =& $a[$key];
		}

		return true;
	}

	/**
	 * Unsets an array element given its path in "dot notation."
	 *
	 * @param array &$array Array to search within.
	 * @param string $path Dot-notated path.
	 *
	 * @return void
	 */
	public static function delete(array &$array, $path)
	{
		if (isset($array[$path]) || false === strpos($path, '.')) {
			unset($array[$path]);
			return;
		}

		$a =& $array;

		$keys = explode('.', $path);
		$count = count($keys);
		$index = 1;

		foreach($keys as $key) {

			if (! array_key_exists($key, $a)) {
				return;
			}

			if ($index === $count) {
				unset($a[$key]);
			} else {
				$a =& $a[$key];
				$index++;
			}
		}
	}

}
