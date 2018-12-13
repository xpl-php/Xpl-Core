<?php

declare(strict_types=1);

namespace Xpl\Html;

use SimpleXMLElement;
use InvalidArgumentException;
use Xpl\Variable;

/**
 * Creates HTML strings.
 *
 * @since 1.0
 */
class Writer
{

	/**
	 * XML (v1)
	 *
	 * @var int
	 */
	const LANG_XML1 = ENT_XML1;

	/**
	 * XHTML
	 *
	 * @var int
	 */
	const LANG_XHTML = ENT_XHTML;

	/**
	 * HTML5
	 *
	 * @var int
	 */
	const LANG_HTML5 = ENT_HTML5;

	/**
	 * The top-level parent element to render.
	 *
	 * @var Element
	 */
	private $element;

	/**
	 * Constant corresponding to the output language.
	 *
	 * @var int
	 */
	private $language = self::LANG_HTML5;

	/**
	 * Self-closing HTML tags.
	 *
	 * @var array
	 */
	private static $selfClosingTags = [
		'hr'		=> 1,
		'br'		=> 1,
		'input'		=> 1,
		'meta'		=> 1,
		'base'		=> 1,
		'basefont'	=> 1,
		'col'		=> 1,
		'frame'		=> 1,
		'link'		=> 1,
		'param'		=> 1
	];

	/**
	 * Create a new writer for the given element tree.
	 *
	 * @param \Html\Element $element The top-level parent element to render.
	 * @param int $language [Optional] The output language to use. Default = Writer::LANG_HTML5
	 */
	public function __construct(Element $element, int $language = self::LANG_HTML5)
	{
		$this->element = $element;
		$this->language = $language;
	}

	/**
	 * Returns the element tree as an HTML string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			return static::render($this->element, $this->language);
		} catch(\Throwable $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
			return '';
		}
	}

	/**
	 * Renders an Element as a string.
	 *
	 * @param  Element $element
	 * @param  int     $lang [Optional]
	 *
	 * @return string
	 */
	public static function render(Element $element, int $lang = self::LANG_HTML5)
	{
		$html = "";
		$element->prepare();

		if ($before = $element->getBeforeElement()) {
			$html .= static::arrayToString($before);
		}

		$html .= static::tag(
			$element->getTag(),
			$element->getAttributes(),
			$element->getHtmlContent(),
			$lang
		);

		if ($after = $element->getAfterElement()) {
			$html .= static::arrayToString($after);
		}

		return $html;
	}

	/**
	 * Generates an HTML tag string.
	 *
	 * @throws InvalidTagException if tag is empty
	 *
	 * @param  string 	$tag
	 * @param  iterable $attributes [Optional]
	 * @param  string 	$content [Optional]
	 * @param  int  	$lang [Optional]
	 *
	 * @return string
	 */
	public static function tag(
		string $tag,
		iterable $attributes = null,
		string $content = "",
		int $lang = self::LANG_HTML5
	) : string
	{
		if (empty($tag)) {
			throw new InvalidTagException("Empty HTML tag.");
		}

		$html = "<$tag";

		if ($attributes) {
			$html .= static::buildAttributeString($attributes, $lang);
		}

		if (static::isSelfClosingTag($tag)) {
			$html .= ($lang === self::LANG_HTML5) ? '>' : ' />';
		} else {
			$html .= '>' . $content . "</{$tag}>";
		}

		return $html;
	}

	/**
	 * Check if a tag is "self-closing".
	 *
	 * @param string $tag
	 *
	 * @return bool
	 */
	public static function isSelfClosingTag(string $tag) : bool
	{
		return isset(self::$selfClosingTags[$tag]);
	}

	/**
	 * Escapes a string for use as an attribute value.
	 *
	 * @param string $string
	 * @param int 	 $lang [Optional]
	 *
	 * @return string
	 */
	public static function escapeAttribute(string $string, int $lang = self::LANG_HTML5) : string
	{
		return htmlentities($string, ENT_QUOTES | ENT_DISALLOWED | $lang);
	}

	/**
	 * Build an attribute string from an iterable or string.
	 *
	 * @param iterable|string	$attributes
	 * @param int 				$lang [Optional]
	 *
	 * @return string
	 */
	public static function buildAttributeString($attributes, int $lang = self::LANG_HTML5) : string
	{
		$str = '';

		if (! is_iterable($attributes)) {
			if (is_string($attributes)) {
				$attributes = static::parseAttributeString($attributes);
			} else {
				throw new InvalidArgumentException(sprintf(
					"Attributes must be string or iterable, given: '%s'.", Variable::type($attributes)
				));
			}
		}

		foreach($attributes as $name => $value) {

			if (is_array($value)) {
				if (array_filter($value, 'is_scalar') === $value) {
					$value = implode(' ', $value);
				} else {
					$value = json_encode($value, JSON_NUMERIC_CHECK);
				}
			}

			$str .= ' ' . $name . '="' . static::escapeAttribute($value, $lang) . '"';
		}

		return $str;
	}

	/**
	 * Parses an attribute string to an array.
	 *
	 * @param string $string
	 *
	 * @return array
	 */
	public static function parseAttributeString(string $string) : array
	{
		$str = trim($string);
		$attributes = [];

		if ($str[0] !== '<') {
			// String does not start with a tag
			$str = "<div $str></div>";
		}

		$simpleXML = new SimpleXMLElement($str);

		foreach($simpleXML->attributes() as $name => $value) {
			$attributes[$name] = (string)$value;
		}

		return $attributes;
	}

	/**
	 * Casts and concatenates an array of values to a single string.
	 *
	 * @param array $arr
	 *
	 * @return string
	 */
	public static function arrayToString(array $arr) : string
	{
		return implode('', array_map('strval', $arr));
	}

}
