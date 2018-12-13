<?php

declare(strict_types=1);

namespace Xpl\Traits;

use Xpl\Arrayable;
use Xpl\Hydratable;

/**
 * Trait to implement Serializable using properties.
 *
 * @since 1.0
 */
trait PropertySerializable
{

	public function serialize()
	{
		if ($this instanceof Arrayable) {
			$object_vars = $this->toArray();
		} else {
			$object_vars = \get_object_vars($this);
		}

		return \serialize($object_vars);
	}

	public function unserialize($serial)
	{
		$object_vars = \unserialize($serial);

		if ($this instanceof Hydratable) {
			$this->hydrate($object_vars);
		} else {
			foreach($object_vars as $key => $value) {
				$this->$key = $value;
			}
		}
	}

}
