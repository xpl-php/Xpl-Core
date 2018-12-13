<?php

declare(strict_types=1);

namespace Xpl\Traits;

/**
 * Trait to implement readable property access.
 *
 * @since 1.0
 */
trait PropertyAccessReadable
{

	public function __get($key)
	{
		return $this->$key;
	}

	public function __isset($key)
	{
		return isset($this->$key);
	}

}
