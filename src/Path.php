<?php

declare(strict_types=1);

namespace Xpl;

/**
 * Path utilities
 *
 * @since 1.0
 */
abstract class Path
{

	/**
	 * Normalizes a path.
	 *
	 * Converts all slashes ("/" and "\") to the system directory separator.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function normalize(string $path) : string
	{
		return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
	}

	/**
	 * Removes trailing slashes ("/" and "\") from the path.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function stripTrailingSlash(string $path) : string
	{
		return rtrim($path, '/\\');
	}

	/**
	 * Ensures the path ends with a slash (the system directory separator).
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function addTrailingSlash(string $path) : string
	{
		return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
	}

	/**
	 * Joins the passed path segments into a single concatenated path.
	 *
	 * @param ... $segments
	 *
	 * @return string
	 */
	public static function join(...$segments) : string
	{
		$first = array_shift($segments);

		$segments = array_map(function ($item) { return trim($item, '/\\'); }, $segments);

		return rtrim($first, '/\\') . implode(DIRECTORY_SEPARATOR, $segments);
	}

	/**
	 * Returns true if $path looks like a URL.
	 *
	 * Valid URLs begin with a scheme ("http", "ftp", etc.) or are scheme-relative ("//").
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isUrl(string $path) : bool
	{
		return ('/' === $path[0] && '/' === $path[1]) || (bool)parse_url($path, PHP_URL_SCHEME);
	}

	/**
	 * Returns true if path looks like an absolute path.
	 *
	 * Operates naively on the input string, and is not aware of the actual
	 * filesystem, or path components such as "..".
	 *
	 * @param string $path Filesystem path
	 *
	 * @return bool True if path is absolute, otherwise false.
	 */
	public static function isAbsolute(string $path) : bool
	{
		if ('/' === $path[0]) {
			return true;
		}

		if ('\\' !== DIRECTORY_SEPARATOR) {
			return self::isUrl($path);
		}

		return (
			$path[0] === '\\'
			|| (strlen($path) > 3 && ctype_alpha($path[0]) && $path[1] == ':' && ($path[2] == '\\' || $path[2] == '/'))
			|| self::isUrl($path)
		);
	}

	/**
	 * Returns true if path is a "dot-file" (e.g. ".htaccess", "/path/to/my/.dot-file")
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isDotFile(string $path) : bool
	{
		return strpos(basename($path), '.') === 0;
	}

	/**
	 * Returns the parent directory path.
	 *
	 * Identical to dirname() with second '$levels' parameter (added PHP7).
	 *
	 * @param string $path
	 * @param int $levels [Optional] Default = 1
	 *
	 * @return string
	 */
	public static function parent(string $path, int $levels = 1) : string
	{
		return dirname($path, $levels);
	}

	/**
	 * Returns the trailing component of $path, excluding any and all file extension(s).
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function filename(string $path) : string
	{
		$basename = basename($path);

		// use ! to match '0' and 'false' to return the full name for both dot-files and files without an extension.
		return ! strpos($basename, '.') ? $basename : strstr($basename, '.', true);
	}

	/**
	 * Returns the path's full file extension, if any.
	 *
	 * This function differs from pathinfo($path, PATHINFO_EXTENSION) in that
	 * it returns all the extensions, not just the last one.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function extension(string $path) : string
	{
		$basename = basename($path);

		// use ! to match '0' and 'false' to return the full name for both dot-files and files without an extension.
		return ! strpos($basename, '.') ? '' : ltrim(strstr($basename, '.'), '.');
	}

	/**
	 * Returns a path relative to a base path.
	 *
	 * @param string $path
	 * @param string $base
	 *
	 * @return string|null
	 */
	public static function relative(string $path, string $base)
	{
		$path = self::normalize($path);
		$base = self::normalize($base);

		if (strpos($path, $base) !== 0) {
			return null;
		}

		return ltrim(substr($path, strlen($base)), DIRECTORY_SEPARATOR);
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns the result.
	 *
	 * Eg:
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 */
	public static function resolve(string $path) : string
	{
		if (DIRECTORY_SEPARATOR === '\\') {
			$path = str_replace('\\', '/', $path);
		}

		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = count($path); $i < $n; $i++) {
			if ($path[$i] == '.' || $path[$i] == '..') {
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == '')) {
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				} else if ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != ''))) {
					unset($path[$i]);
					unset($path[$i - 1]);
					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode(DIRECTORY_SEPARATOR, $path);
	}
}
