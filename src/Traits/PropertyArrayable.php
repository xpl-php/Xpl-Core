<?php

declare(strict_types=1);

namespace Xpl\Traits;

/**
 * Trait to implement Xpl\Arrayable using properties.
 *
 * @since 1.0
 */
trait PropertyArrayable
{

	public function toArray() : array
	{
		return \get_object_vars($this);
	}
}
