<?php

declare(strict_types=1);

namespace Xpl\Traits;

/**
 * Trait to implement ArrayAccess using properties.
 *
 * Implementations MUST NOT implement Xpl\Immutable
 *
 * @since 1.0
 */
trait PropertyArrayAccess
{

	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

}
