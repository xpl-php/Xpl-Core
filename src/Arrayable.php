<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Interface for classes that can return itself as a php array.
 *
 * @since 1.0
 */
interface Arrayable
{

	/**
	 * Returns the object as a PHP array.
	 *
	 * @return array
	 */
	public function toArray() : array;

}
