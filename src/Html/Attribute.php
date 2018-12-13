<?php

declare(strict_types=1);

namespace Xpl\Html;

use Xpl\Arrayable;

class Attribute implements Arrayable
{

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param mixed $value [Optional]
	 */
	public function __construct(string $name, $value = null)
	{
		$this->name = $name;

		if (isset($value)) {
			$this->setValue($value);
		}
	}

	/**
	 * Returns the attribute name.
	 *
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * Sets the attribute value list.
	 *
	 * @param  mixed $value
	 *
	 * @return Attribute
	 */
	public function setValue($value) : Attribute
	{
		$this->values = is_array($value) ? $value : [$value];

		return $this;
	}

	/**
	 * Adds a value to the attributes value list.
	 *
	 * @param  mixed $value
	 *
	 * @return Attribute
	 */
	public function addValue($value) : Attribute
	{
		if (is_array($value)) {
			$this->values = array_merge($this->values, $value);
		} else if (! $this->hasValue($value)) {
			$this->values[] = $value;
		}

		return $this;
	}

	/**
	 * Checks whether the given value is in the attribute's value list.
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function hasValue($value) : bool
	{
		return in_array($value, $this->values, true);
	}

	/**
	 * Returns the attribute's value list.
	 *
	 * @return array
	 */
	public function getValues() : array
	{
		return $this->values;
	}

	/**
	 * Returns an item from the attribute's value list at the given index.
	 *
	 * @param  int    $index
	 *
	 * @return mixed
	 */
	public function getValue(int $index)
	{
		return $this->values[$index] ?? null;
	}

	/**
	 * Removes a given value from the attribute's value list.
	 *
	 * @param mixed $value
	 *
	 * @return Attribute
	 */
	public function removeValue($value) : Attribute
	{
		$key = array_search($value, $this->values, true);

		if ($key !== false) {
			unset($this->values[$key]);
		}

		return $this;
	}

	/**
	 * Returns the attribute as a string suitable for an HTML tag.
	 *
	 * @return string
	 */
	public function __toString()
	{
		if (empty($this->values)) {
			$valueString = $this->name;
		} else {
			$valueString = implode(' ', $this->values);
		}

		return sprintf('%s="%s"', $this->name, $valueString);
	}

	/**
	 * Returns the attribute name and value list as an array.
	 *
	 * @return array
	 */
	public function toArray() : array
	{
		return [$this->name => $this->values];
	}

}
