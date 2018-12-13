<?php

declare(strict_types=1);

namespace Xpl\Traits;

/**
 * Trait to implement writable property access.
 *
 * Implementations MUST NOT implement Xpl\Immutable
 *
 * @since 1.0
 */
trait PropertyAccessWritable
{

	public function __set($key, $value)
	{
		$this->$key = $value;
	}

	public function __unset($key)
	{
		unset($this->$key);
	}

}
