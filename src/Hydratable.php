<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Interface for classes that can be hydrated with data from an iterable.
 *
 * Implementations MAY implement Xpl\Immutable if and ONLY IF hydration is allowed
 * exactly once upon instantiation - subsequent calls to hydrate() MUST throw an exception.
 *
 * @since 1.0
 */
interface Hydratable
{

	/**
	 * Hydrates the object with data.
	 *
	 * @param  iterable $data
	 */
	public function hydrate(iterable $data);

}
