<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Number utilities.
 *
 * @since 1.0
 */
abstract class Number
{

	/**
	 * Parses a numeric string to a integer- or float-castable number.
	 *
	 * @param string $number
	 *
	 * @return string
	 */
	public static function parse($number) : string
	{
		return (string)filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}

	/**
	 * Parses a number with a "byte suffix" to a float.
	 *
	 * Recognized suffixes include:
	 *   "T" = 1024^4 bytes
	 *   "G" = 1024^3 bytes
	 *   "M" = 1024^2 bytes
	 *   "K" = 1024   bytes
	 *
	 * Useful for ini values such as 'post_size_max'
	 *
	 * @param string $bytes
	 *
	 * @return float
	 */
	public static function parseBytes(string $bytes) : float
	{
		$bytes = trim($bytes);

		if (ctype_digit($bytes)) {
			return floatval($bytes);
		}

		$suffix = $bytes[-1];
		$bytes = substr($bytes, 0, -1);

		switch(strtoupper($suffix)) {
			case 'T':
				$bytes *= 1024;
			case 'G':
				$bytes *= 1024;
			case 'M':
				$bytes *= 1024;
			case 'K':
				$bytes *= 1024;
		}

		return $bytes;
	}

	/**
	 * Tests whether a number is a "mixed number" - i.e. has a fractional component.
	 *
	 * @param number $number
	 *
	 * @return bool
	 */
	public static function isMixed($number) : bool
	{
		return floor($number) !== floatval($number);
	}

	public static function isEven($number) : bool
	{
		return ! ($number % 2);
	}

	public static function isOdd($number) : bool
	{
		return !! ($number % 2);
	}

	/**
	 * Casts a numeric string to its "natural" type, either a integer or float.
	 *
	 * @param number $number
	 *
	 * @return int|float
	 */
	public static function natcast($number)
	{
		return self::isMixed($number) ? floatval($number) : intval($number);
	}

	/**
	 * Rounds a number to a given number of significant digits.
	 *
	 * @param number $number
	 * @param int $digits
	 *
	 * @return float
	 */
	public static function roundsd($number, int $digits) : float
	{
		return round($number, $digits - floor(log10($number)) - 1);
	}

	/**
	 * Formats a number, optionally using locale-specific formatting.
	 *
	 * @param number $value
	 * @param int 	 $precision [Optional] Default = 2
	 * @param bool   $use_locale [Optional] Default = false
	 *
	 * @return string
	 */
	public static function format($value, int $precision = 2, bool $use_locale = false) : string
	{
		if (! $use_locale) {
			return number_format($value, $precision);
		}

		$info = localeconv();

		return number_format($number, $precision, $info['decimal_point'], $info['thousands_sep']);
	}

	/**
	 * Formats a monetary number using locale-specific formatting.
	 *
	 * @param  number $value
	 * @param  int    $precision [Optional] Default = 2
	 *
	 * @return string
	 */
	public static function formatMoney($value, int $precision = 2) : string
	{
		$info = localeconv();

		return number_format($number, $precision, $info['mon_decimal_point'], $info['mon_thousands_sep']);
	}

	/**
	 * Formats a large number to an abbreviated string.
	 *
	 * @example
	 * Number::humanize(1111100, 2)			=> "1.1 million"
	 * Number::humanize(1111100, 6)			=> "1.1111 million"
	 * Number::humanize(1111100, 6, true)	=> "1.11110 million"
	 *
	 * @param number $value
	 * @param number $digits [Optional] Default = 4
	 * @param bool   $format [Optional] Default = false
	 *
	 * @return string
	 */
	public static function humanize($value, int $digits = 4, bool $format = false) : string
	{
		static $descriptor = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion'];

		$index = min([floor(log($value, 1000)), 6]); // count($descriptor) - 1 = 6
		$number = $value / pow(1000, $index);
		$decimals = $digits - floor(log10($number)) - 1;
		$number = $format ? number_format($number, $decimals) : round($number, $decimals);

		return trim($number.' '.$descriptor[$index]);
	}

	/**
	 * Format bytes to SI or binary (IEC) units.
	 *
	 * @param number $bytes Number of bytes.
	 * @param bool $binary Whether to use binary (IEC) units. Default = true
	 *
	 * @return string Formatted bytes with abbreviated unit.
	 */
	public static function formatBytes($bytes, bool $binary = true, int $decimals = 2) : string
	{
		if ($binary) {
			$prefixes = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'EiB', 'ZiB', 'YiB'];
			$base = 1024;
		} else {
			$prefixes = ['B', 'kB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB'];
			$base = 1000;
		}

		$index = min([intval(log($bytes, $base)), 7]);

		return number_format($bytes / pow($base, $index), $decimals).' '.$prefixes[$index];
	}

}
