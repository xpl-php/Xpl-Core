<?php

declare(strict_types=1);

namespace Xpl\Traits;

/**
 * Trait to implement Xpl\Hydratable using properties.
 *
 * @since 1.0
 */
trait PropertyHydratable
{

	public function hydrate(iterable $data)
	{
		foreach($data as $key => $value) {
			$this->$key = $value;
		}
	}

}
