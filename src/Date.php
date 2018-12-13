<?php

declare(strict_types=1);

namespace Xpl;

use DateTime;
use DateTimeZone;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * Date utilities
 *
 * @since 1.0
 */
abstract class Date
{

	/**
	 * @var int Number of seconds in a minute
	 */
	const MINUTE = 60;

	/**
	 * @var int Number of seconds in an hour
	 */
	const HOUR = 3600;

	/**
	 * @var int Number of seconds in a day
	 */
	const DAY = 86400;

	/**
	 * @var int Number of seconds in a week
	 */
	const WEEK = 604800;

	/**
	 * @var int Number of seconds in a month (30 days)
	 */
	const MONTH = 2592000;

	/**
	 * @var int Number of seconds in a year (365 days)
	 */
	const YEAR = 31536000;

	/**
	 * @var string "SQL" date format
	 */
	const FORMAT_SQL = "Y-m-d H:i:s";

	/**
	 * @var string Default "human" date format
	 */
	const FORMAT_HUMAN_DEFAULT = 'd M Y H:i';

	/**
	 * Named date formats.
	 *
	 * @var string[]
	 */
	private static $formats = [
		'sql'	=> self::FORMAT_SQL,
		'human' => self::FORMAT_HUMAN_DEFAULT,
		'atom' => DateTimeInterface::ATOM,
		'cookie' => DateTimeInterface::COOKIE,
		'rss' => DateTimeInterface::RSS,
		'w3c' => DateTimeInterface::W3C
	];

	/**
	 * Checks if the given argument is a DateTimeInterface or valid date/time.
	 *
	 * @param \DateTimeInterface|string|int $dateTime
	 *
	 * @return bool
	 */
	public static function isValid($dateTime) : bool
	{
		if ($dateTime instanceof DateTimeInterface) {
			return true;
		}

		$time = strtotime($dateTime);

		return $time !== false && $time > 0;
	}

	/**
	 * Returns a Unix time, or the current time if no argument is given.
	 *
	 * @param \DateTimeInterface|string|int $time [Optional]
	 *
	 * @return int
	 */
	public static function time($time = null) : int
	{
		if (is_null($time)) {
			return time();
		}

		if ($time instanceof DateTimeInterface) {
			return $time->format("U");
		}

		if (is_numeric($time)) {
			return intval($time);
		}

		return strtotime($time) ?: 0;
	}

	/**
	 * Creates a DateTime object from a given time and timezone.
	 *
	 * @param \DateTimeInterface|string|int $time [Optional] Default = current time
	 * @param \DateTimeZone|string $timezone [Optional] Default = current timezone
	 *
	 * @throws InvalidArgumentException if $time is an object and not a DateTimeInterface
	 *
	 * @return \DateTimeInterface
	 */
	public static function make($time = null, $timezone = null) : DateTimeInterface
	{
		$timezone = static::timezone($timezone);

		if ($time && is_object($time)) {

			if (! $time instanceof DateTimeInterface) {
				throw new InvalidArgumentException(sprintf(
					'Time object argument must be DateTimeInterface, given: "%s"', get_class($time)
				));
			}

			$time->setTimezone($timezone);

			return $time;
		}

		$dateTime = new DateTime("@".static::time($time));

		$dateTime->setTimezone($timezone);

		return $dateTime;
	}

	/**
	 * Creates a DateTimeZone object.
	 *
	 * @param \DateTimeZone|string $timezone [Optional] Default = current timezone
	 *
	 * @return \DateTimeZone
	 */
	public static function timezone($timezone = null) : DateTimeZone
	{
		if (! $timezone) {
			$timezone = date_default_timezone_get();
		} else if ($timezone instanceof DateTimeZone) {
			return $timezone;
		}

		return new DateTimeZone($timezone);
	}

	/**
	 * Formats a date or DateTimeInterface.
	 *
	 * @param \DateTimeInterface|string|int $dateTime
	 * @param string $format
	 *
	 * @return string
	 */
	public static function format($dateTime, string $format) : string
	{
		if (isset(self::$formats[$format])) {
			$format = self::$formats[$format];
		}

		return self::make($dateTime)->format($format);
	}

	/**
	 * Returns a human-readable date string from a date or DateTimeInterface.
	 *
	 * @param \DateTimeInterface|string|int $dateTime
	 * @param string $format [Optional] Default is the 'human' format in Date::$formats
	 *
	 * @return string
	 */
	public static function formatHuman($dateTime, string $format = null) : string
	{
		if (! $format) {
			$format = self::getFormat('human');
		}

		return self::make($dateTime)->format($format);
	}

	/**
	 * Returns a date or DateTimeInterface as a string in the format "Y-m-d H:i:s".
	 *
	 * @param \DateTimeInterface|string|int $dateTime
	 *
	 * @return string
	 */
	public static function formatSql($dateTime) : string
	{
		return self::make($dateTime)->format(self::FORMAT_SQL);
	}

	/**
	 * Returns the month name for a given \DateTimeInterface or integer
	 * representing the month number (1-12).
	 *
	 * @param int|\DateTimeInterface $dateTime
	 *
	 * @return string
	 */
	public static function getMonthName($dateTime) : string
	{
		if (is_int($dateTime)) {
			$date = DateTime::createFromFormat('!m', $dateTime);
		} else {
			$date = self::make($dateTime);
		}

		return $date->format('F');
	}

	/**
	 * Sets a named format used by Date::format().
	 *
	 * @param string $format
	 */
	public static function setFormat(string $name, string $format)
	{
		self::$formats[$name] = $format;
	}

	/**
	 * Returns a named format used by Date::format().
	 *
	 * @return string
	 */
	public static function getFormat(string $name) : string
	{
		return self::$formats[$name];
	}

}
