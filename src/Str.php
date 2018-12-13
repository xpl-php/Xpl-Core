<?php

declare(strict_types=1);

namespace Xpl;

/**
 * String utilities.
 *
 * @since 1.0
 */
abstract class Str
{

	/**
	 * Checks whether $haystack starts with $needle.
	 *
	 * @param string $haystack String to search within.
	 * @param string $needle String to find.
	 * @param bool $match_case [Optional] Default = true. Set to false for case-insensitive match.
	 *
	 * @return bool
	 */
	public static function startsWith(string $haystack, string $needle, bool $match_case = true) : bool
	{
		return $match_case
			? 0 === mb_strpos($haystack, $needle)
			: 0 === mb_stripos($haystack, $needle);
	}

	/**
	 * Checks whether $haystack ends with $needle.
	 *
	 * @param string $haystack String to search within.
	 * @param string $needle String to find.
	 * @param bool $match_case [Optional] Default = true. Set to false for case-insensitive match.
	 *
	 * @return bool
	 */
	public static function endsWith(string $haystack, string $needle, bool $match_case = true) : bool
	{
		return $match_case
			? 0 === strcmp($needle, substr($haystack, -strlen($needle)))
			: 0 === strcasecmp($needle, substr($haystack, -strlen($needle)));
	}

	/**
	 * Determine if a given string contains any of the given substrings.
	 *
	 * @author laravel
	 *
	 * @param string $haystack
	 * @param string|array $needles
	 *
	 * @return bool
	 */
	public static function contains(string $haystack, $needles) : bool
	{
		foreach((array)$needles as $needle) {
			if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks whether $string contains ANY of the characters in $charmask.
	 *
	 * @param string $string
	 * @param string $charmask
	 *
	 * @return bool
	 */
	public static function containsChars(string $string, string $charmask) : bool
	{
		return strlen($string) !== strcspn($string, $charmask);
	}

	/**
	 * Check if the string contains multibyte characters.
	 *
	 * @param string $string value to test
	 *
	 * @return bool
	 */
	public static function isMultibyte(string $string) : bool
	{
		$length = strlen($string);

		for ($i = 0; $i < $length; $i++) {
			if (ord($string[$i]) > 128) {
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Returns whether the given variable is a valid JSON string.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isJson(string $string) : bool
	{
		if (empty($string)) {
			return false;
		}

		if ($string === '{}' || $string === '[]') {
			return true;
		}

		@json_decode($string);

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Checks whether the given value is a valid XML string.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function isXml(string $string) : bool
	{
		if (empty($string) || ! self::startsWith($string, '<?xml ', false)) {
			return false;
		}

		$result = false;
		$use_errors = libxml_use_internal_errors(true);

		if (simplexml_load_string($string) instanceof \SimpleXMLElement && libxml_get_last_error() === false) {
			$result = true;
		}

		libxml_use_internal_errors($use_errors);

		return $result;
	}

	/**
	 * Returns the string as a boolean value.
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public static function toBool(string $string) : bool
	{
		if (empty($string)) {
			return false;
		}

		if (is_numeric($string)) {
			return $string > 0;
		}

		$filtered = filter_var($string, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		if (is_null($filtered)) {
			return (bool)$string;
		}

		return $filtered;
	}

	/**
	 * Strips non-alphanumeric characters from a string.
	 *
	 * @param string $string String to escape.
	 *
	 * @return string Alphanumeric string.
	 */
	public static function toAlnum(string $string) : string
	{
		if (ctype_alnum($string)) {
			return $string;
		}

		return preg_replace('/[^a-zA-Z0-9]/', '', $string);
	}

	/**
	 * Formats a string by injecting non-numeric characters into the string
	 * in the positions they appear in the template.
	 *
	 * @param string $string	The string to format.
	 * @param string $template	String format to apply.
	 *
	 * @return string Formatted string.
	 */
	public static function format(string $string, string $template) : string
	{
		$result = '';
		$fpos = $spos = 0;
		$tmpl_length = strlen($template) - 1;

		while ($tmpl_length >= $fpos) {

			$chr = substr($template, $fpos, 1);

			if (ctype_alnum($chr)) {
				$result .= substr($string, $spos, 1);
				$spos++;
			} else {
				$result .= $chr;
			}

			$fpos++;
		}

		return $result;
	}

	/**
	 * Formats a phone number based on string length.
	 *
	 * @param string $phone Unformatted phone number.
	 *
	 * @return string Formatted phone number based on number of characters.
	 */
	public static function formatPhoneNumber(string $phone) : string
	{
		// remove any pre-existing formatting characters
		$string = str_replace(['(',')','+','-',' '], '', $phone);

		switch(strlen($string)) {
			case 7:
				$tmpl = '000-0000';
				break;
			case 10:
				$tmpl = '(000) 000-0000';
				break;
			case 11:
				$tmpl = '+0 (000) 000-0000';
				break;
			case 12:
				$tmpl = '+00 00 0000 0000';
				break;
			default:
				// No known length format, so return the original string
				return $phone;
		}

		return self::format($string, $tmpl);
	}

	/**
	 * Converts a string to a PEAR-like class name. (e.g. "Http_Request2_Response")
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function pearCase(string $string) : string
	{
		if (ctype_alnum($string)) {
			return ucfirst($string);
		}

		return preg_replace('/[_]{2,}/', '_',
			str_replace(' ', '_',
				ucwords(preg_replace('/[^a-zA-Z0-9]/', '_',
					trim(preg_replace('/[A-Z]/', ' $0', $string))
				))
			)
		);
	}

	/**
	 * Converts a string to "snake_case"
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function snakeCase(string $string) : string
	{
		return strtolower(self::pearCase($string));
	}

	/**
	 * Converts a string to "StudlyCaps"
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function studlyCase(string $string) : string
	{
		if (ctype_lower($string)) {
			return ucfirst($string);
		}

		return str_replace(' ', '', ucwords(trim(preg_replace('/[^a-zA-Z0-9]/', ' ', $string))));
	}

	/**
	 * Converts a string to "camelCase"
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function camelCase(string $string) : string
	{
		return lcfirst(self::studlyCase($string));
	}

	/**
	 * Normalizes EOL characters.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function normalizeLineEndings(string $string) : string
	{
		return str_replace("\r", "\n", trim($string));
	}

	/**
	 * Normalize EOL characters and strip duplicate whitespace.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function collapseWhitespace(string $string) : string
	{
		$string = trim($string);

		if (ctype_alnum($string)) {
			return $string;
		}

		return preg_replace(['/\n+/', '/[ \t]+/'], ["\n", ' '], self::normalizeLineEndings($string));
	}

	/**
	 * Removes single quotes, double quotes, and backticks.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function stripQuotes(string $string) : string
	{
		if (ctype_alnum($string)) {
			return $string;
		}

		return preg_replace("/[\"\\'\\’]/", '', $string);
	}

	/**
	 * Removes non-printing ASCII control characters from a string.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function stripControlChars(string $string) : string
	{
		return preg_replace('/[\x00-\x08\x0B-\x1F]/', '', $string);
	}

	/**
	 * Strips unescaped unicode characters (e.g. u00a0).
	 *
	 * @uses mb_detect_encoding()
	 * @uses mb_convert_encoding()
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function stripInvalidUnicode(string $string) : string
	{
		$encoding = mb_detect_encoding($string);

		if ('UTF-8' !== $encoding && 'ASCII' !== $encoding) {

			$subchr = ini_set('mbstring.substitute_character', 'none');
			$string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

			if ($subchr !== false) {
				ini_set('mbstring.substitute_character', $subchr);
			}
		}

		return stripcslashes(preg_replace('/\\\\u([0-9a-f]{4})/i', '', $string));
	}

}
