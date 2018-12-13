<?php

declare(strict_types=1);

namespace Xpl\Html;

use ArrayAccess;
use Serializable;
use Xpl\Traits\PropertySerializable;

/**
 * Class representing an HTML element.
 * -------------------------------------------------
 * Elements are rendered using the following format:
 *
 * 		$beforeElement
 * 		<$tag $attributes>
 * 			$beforeContent
 * 			if ($childrenBeforeContent) {
 * 				$children
 * 				$content
 * 			} else {
 * 				$content
 *  			$children
 * 			}
 * 			$afterContent
 * 		</$tag>
 * 		$afterElement
 * -------------------------------------------------
 * @since 1.0
 */
class Element implements ArrayAccess, Serializable
{

	use PropertySerializable;

	/**
	 * The element's tag name.
	 *
	 * @var string
	 */
	private $_tag = 'span';

	/**
	 * This element's parent Element instance.
	 *
	 * @var Element
	 */
	private $_parent;

	/**
	 * Array of element attributes.
	 *
	 * @var Attribute[]
	 */
	private $_attributes = [];

	/**
	 * Array of element children.
	 *
	 * @var Element[]
	 */
	private $_children = [];

	/**
	 * An array of strings rendered before the element's opening tag.
	 *
	 * @var string[]
	 */
	private $_beforeElement = [];

	/**
	 * An array of strings rendered after the element's closing tag.
	 *
	 * @var string[]
	 */
	private $_afterElement = [];

	/**
	 * An array of strings rendered before the element's inner content.
	 *
	 * @var string[]
	 */
	private $_beforeContent = [];

	/**
	 * An array of strings rendered after the element's inner content.
	 *
	 * @var string[]
	 */
	private $_afterContent = [];

	/**
	 * Whether to render child elements before this element's content.
	 *
	 * Default = false.
	 *
	 * @var bool
	 */
	private $_childrenBeforeContent = false;

	/**
	 * The element's inner content (excludes children).
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * Constructor. Accepts a parent element.
	 *
	 * @param Element $parent [Optional] Parent element
	 */
	public function __construct(Element $parent = null)
	{
		if ($parent) {
			$this->setParent($parent);
		}

		$this->initialize();
	}

    /**
     * Sets the element's HTML tag.
     *
     * @param string $tag
     *
     * @return Element
     *
     * @throws InvalidTagException if tag is empty
     */
	public function setTag(string $tag) : Element
	{
		$tag = trim($tag);

		if (empty($tag)) {
			throw new InvalidTagException();
		}

		$this->_tag = strtolower($tag);

		return $this;
	}

    /**
     * Returns the element's HTML tag.
     *
     * @return string
     */
	public function getTag() : string
	{
		return $this->_tag;
	}

	/**
	 * Get or set an Attribute object, creating if it does not exist.
	 *
	 * @param string $name
	 * @param Attribute $object [Optional]
	 *
	 * @return Attribute
	 */
	public function attribute(string $name, Attribute $object = null) : Attribute
	{
		if ($object || ! isset($this->_attributes[$name])) {
			$this->_attributes[$name] = $object ?: new Attribute($name);
		}

		return $this->_attributes[$name];
	}

	/**
	 * Alias of Element::attribute()
	 */
	public function attr(string $name, Attribute $object = null) : Attribute
	{
		return $this->attribute($name, $object);
	}

	/**
	 * Returns a data Attribute object.
	 *
	 * @param string $name
	 *
	 * @return Attribute
	 */
	public function data(string $name) : Attribute
	{
		return $this->attribute("data-{$name}");
	}

    /**
     * Sets a single attribute value.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Element
     */
	public function setAttribute(string $name, $value) : Element
	{
        $this->attribute($name)->setValue($value);

		return $this;
	}

    /**
     * Sets the element's attribute values.
     *
     * @param iterable $attributes
     *
     * @return Element
     */
	public function setAttributes(iterable $attributes) : Element
	{
        foreach($attributes as $name => $value) {
			$this->setAttribute($name, $value);
		}

		return $this;
	}

    /**
     * Adds an attribute to the element.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Element
     */
	public function addAttribute(string $name, $value) : Element
	{
		$this->attribute($name)->addValue($value);

		return $this;
	}

    /**
     * Adds multiple attributes to the element.
     *
     * @param iterable $attributes
     *
     * @return Element
     */
	public function addAttributes(iterable $attributes) : Element
	{
        foreach($attributes as $name => $value) {
			$this->addAttribute($name, $value);
		}

		return $this;
	}

    /**
     * Returns a map of attribute values by name.
     *
     * @param string $name
     *
     * @return array
     */
	public function getAttribute(string $name) : array
	{
		return $this->attribute($name)->getValues();
	}

    /**
     * Returns a map of the element's attribute names and values.
     *
     * @return array
     */
	public function getAttributes() : array
	{
		$attrs = [];

		foreach($this->_attributes as $attribute) {
			$attrs[$attribute->getName()] = $attribute->getValues();
		}

		return $attrs;
	}

    /**
     * Checks whether the element has a given attribute.
     *
     * @param string $name
     * @param mixed value [Optional]
     *
     * @return bool
     */
	public function hasAttribute(string $name, $value = null) : bool
	{
		if (! isset($this->_attributes[$name])) {
			return false;
		}

		if (is_null($value)) {
			return true;
		}

		return $this->attribute($name)->hasValue($value);
	}

	/**
	 * Removes an attribute from the element.
	 *
	 * @param string $name
	 * @param mixed $value [Optional]
	 *
	 * @return Element
	 */
	public function removeAttribute(string $name, $value = null) : Element
	{
		if (is_null($value)) {
			unset($this->_attributes[$name]);
		} else if ($this->hasAttribute($name)) {
			$this->attribute($name)->removeValue($value);
		}

		return $this;
	}

	/**
	 * Sets the element's parent.
	 *
	 * @param Element $element
	 *
	 * @return Element
	 */
	public function setParent(Element $element) : Element
	{
		$this->_parent = $element;

		return $this;
	}

	/**
	 * Returns the element's parent element.
	 *
	 * @return Element|null
	 */
	public function getParent()
	{
		return $this->_parent;
	}

	/**
	 * Sets the element's children.
	 *
	 * @param iterable $elements
	 *
	 * @throws InvalidElementException if any children are not Element objects.
	 *
	 * @return Element
	 */
	public function setChildren(iterable $elements) : Element
	{
		$children = [];

		foreach($elements as $el) {

			if (! $el instanceof Element) {
				throw new InvalidElementException(sprintf(
					'Invalid child element: expecting Html\Element, given "%s"',
					(is_object($el) ? get_class($el) : gettype($el))
				));
			}

			$el->setParent($this);

			$children[] = $el;
		}

		$this->_children = $children;

		return $this;
	}

	public function getChildren() : array
	{
		return $this->_children;
	}

	public function hasChildren() : bool
	{
		return ! empty($this->_children);
	}

	public function addChild(Element $element = null) : Element
	{
		if (! $element) {
			$element = new Element();
		}

		$element->setParent($this);

		$this->_children[] = $element;

		return $element;
	}

	public function getChild(int $index)
	{
		return $this->_children[$index] ?? null;
	}

	public function hasChild(int $index) : bool
	{
		return isset($this->_children[$index]);
	}

	public function isChild(Element $element) : bool
	{
		return in_array($element, $this->_children, true);
	}

	public function deleteChild($child) : Element
	{
		if ($child instanceof Element) {

			$index = array_search($child, $this->_children, true);

			if ($index !== false) {
				unset($this->_children[$index]);
			}

		} else {
			unset($this->_children[(int)$child]);
		}

		return $this;
	}

    /**
     * Sets the content.
     *
     * @param string $content
     *
     * @return Element
     */
	public function setContent(string $content) : Element
	{
		$this->content = $content;

		return $this;
	}

    /**
     * Returns the content.
     *
     * @return string
     */
	public function getContent() : string
	{
		return $this->content;
	}

	/**
	 * Adds content to the "before element" content array.
	 *
	 * @param string $content
	 *
	 * @return Element
	 */
	public function beforeContent(string $content) : Element
	{
		array_unshift($this->_beforeContent, $content);

        return $this;
	}

	/**
	 * Sets the "before content" content.
	 *
	 * @param  string  $content
	 *
	 * @return Element
	 */
	public function setBeforeContent(string $content) : Element
	{
		$this->_beforeContent = [$content];

		return $this;
	}

	/**
	 * Returns the "before content" content array.
	 *
	 * @return array
	 */
	public function getBeforeContent() : array
	{
		return $this->_beforeContent;
	}

	/**
	 * Adds content to the "after element" content array.
	 *
	 * @param string $content
	 *
	 * @return Element
	 */
	public function afterContent(string $content) : Element
	{
		$this->_afterContent[] = $content;

        return $this;
	}

	/**
	 * Sets the "after content" content.
	 *
	 * @param  string  $content
	 *
	 * @return Element
	 */
	public function setAfterContent(string $content) : Element
	{
		$this->_afterContent = [$content];

        return $this;
	}

	/**
	 * Returns the "after content" content array.
	 *
	 * @return array
	 */
	public function getAfterContent() : array
	{
		return $this->_afterContent;
	}

	/**
	 * Adds content to the "before element" content array.
	 *
	 * @param string $content
	 *
	 * @return Element
	 */
	public function beforeElement(string $content) : Element
	{
		array_unshift($this->_beforeElement, $content);

        return $this;
	}

	/**
	 * Adds content to the "after element" content array.
	 *
	 * @param string $content
	 *
	 * @return Element
	 */
	public function afterElement(string $content) : Element
	{
		$this->_afterElement[] = $content;

        return $this;
	}

	/**
	 * Returns the "before element" content array.
	 *
	 * @return array
	 */
	public function getBeforeElement() : array
	{
		return $this->_beforeElement;
	}

	/**
	 * Returns the "after element" content array.
	 *
	 * @return array
	 */
	public function getAfterElement() : array
	{
		return $this->_afterElement;
	}

	/**
	 * Sets the "before element" content.
	 *
	 * @param  string  $content
	 *
	 * @return Element
	 */
	public function setBeforeElement(string $content) : Element
	{
		$this->_beforeElement = [$content];

		return $this;
	}

	/**
	 * Sets the "after element" content.
	 *
	 * @param  string  $content
	 *
	 * @return Element
	 */
	public function setAfterElement(string $content) : Element
	{
		$this->_afterElement = [$content];

        return $this;
	}

	/**
	 * Sets whether to render child content before the element's content.
	 *
	 * By default, this setting is false.
	 *
	 * @param  bool $value [Optional]
	 *
	 * @return Element
	 */
	public function renderChildrenBeforeContent(bool $value = true) : Element
	{
		$this->_childrenBeforeContent = $value;

		return $this;
	}

	/**
	 * Returns the content of any and all children.
	 *
	 * @return string
	 */
	public function getChildContent() : string
	{
		$content = '';

		foreach($this->_children as $child) {
			$content .= (string)$child;
		}

		return $content;
	}

	/**
	 * Returns the full element content, including children.
	 *
	 * @return string
	 */
	public function getHtmlContent() : string
	{
		$html = "";

		if ($beforeContent = $this->getBeforeContent()) {
			$html .= implode('', $beforeContent);
		}

		if ($this->_childrenBeforeContent) {
			$html .= $this->getChildContent() . $this->getContent();
		} else {
			$html .= $this->getContent() . $this->getChildContent();
		}

		if ($afterContent = $this->getAfterContent()) {
			$html .= implode('', $afterContent);
		}

		return $html;
	}

	/** ------------------------------------------------
	 * Implement ArrayAccess
	 * ---------------------------------------------- */

	/**
	 * Alias of Element::getAttribute()
	 */
	public function offsetGet($offset)
	{
		return $this->getAttribute($offset);
	}

	/**
	 * Alias of Element::hasAttribute()
	 */
	public function offsetExists($offset)
	{
		return $this->hasAttribute($offset);
	}

	/**
	 * Alias of Element::setAttribute()
	 */
	public function offsetSet($offset, $value)
	{
		$this->setAttribute($offset, $value);
	}

	/**
	 * Alias of Element::removeAttribute()
	 */
	public function offsetUnset($offset)
	{
		$this->removeAttribute($offset);
	}

	/** ------------------------------------------------
	 * Implement __toString
	 * ---------------------------------------------- */

    /**
     * Returns the element as HTML.
     *
     * @return string
     */
	public function __toString()
	{
       return (string)new Writer($this);
	}

	/** ------------------------------------------------
	 * Subclass methods
	 * ---------------------------------------------- */

    /**
     * Called at start of __toString() before any HTML is generated.
     *
     * In subclasses, this method can be used to print sub-elements in the current element's content.
     */
	public function prepare()
	{

	}

	/**
	 * Called at end of constructor to initialize the element.
	 *
	 * By default, calls Element::getDefaultAttributes() to set the element's initial attributes.
	 *
	 * Subclasses can overwrite this method to implement custom functionality upon element creation.
	 */
	protected function initialize()
	{
		$this->setAttributes($this->getDefaultAttributes());
	}

	/**
	 * Returns the attributes to add to the element when created.
	 *
	 * @return array
	 */
	protected function getDefaultAttributes() : array
	{
		return [];
	}

}
