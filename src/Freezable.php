<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Interface for classes that can be "frozen" in a particular state.
 *
 * @since 1.0
 */
interface Freezable
{

	/**
	 * Freeze the object in its current state.
	 *
	 * @return void
	 */
	public function freeze();

	/**
	 * Unfreeze the object from its current state.
	 *
	 * @return void
	 */
	public function unfreeze();

	/**
	 * Whether the object's state is currently frozen.
	 *
	 * @return bool
	 */
	public function isFrozen() : bool;

}
