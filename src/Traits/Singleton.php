<?php

declare(strict_types=1);

namespace Xpl\Traits;

use ReflectionClass;

/**
 * Singleton trait.
 *
 * @since 1.0
 */
trait Singleton
{

	/**
	 * Returns the singleton instance of the calling class.
	 *
	 * @param mixed ...$args [Optional]
	 *
	 * @return Singleton
	 */
	public static function instance(...$args) : Singleton
	{
		static $instance;

		if (! isset($instance)) {
			$instance = $args
				? (new ReflectionClass(static::class))->newInstanceArgs($args)
				: new static();
		}

		return $instance;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * Singleton via the `new` operator from outside of this class.
	 */
	protected function __construct() {}

	/**
	 * Private clone method to prevent cloning of the Singleton instance.
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Private unserialize method to prevent unserializing of the Singleton
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}

}
