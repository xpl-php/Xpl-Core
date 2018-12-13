<?php

declare(strict_types=1);

namespace Xpl;

use Xpl\Wrapper\Number as NumberWrapper;

/**
 * Math utilities
 */
abstract class Math
{

	/**
	 * Default bcmath scale value.
	 *
	 * @var string
	 */
	const SCALE = 128;

	/**
	 * Checks whether $number is a numeric primitive or instance of Xpl\Wrapper\Number
	 *
	 * @param mixed $number
	 *
	 * @return bool
	 */
	public static function isNumber($number) : bool
	{
		return $number instanceof NumberWrapper || is_numeric($number);
	}

	/**
	 * Filters out non-number values from an array.
	 *
	 * @see Math::isNumber()
	 *
	 * @param  array $array
	 *
	 * @return array
	 */
	public static function filter(array $array) : array
	{
		return array_filter($array, [static::class, 'isNumber']);
	}

	/**
	 * Counts numeric values in an array.
	 *
	 * @param array $array
	 * @return string
	 */
	public static function count(array $array)
	{
		return count(self::filter($array));
	}

	/**
	 * Adds two numbers.
	 *
	 * @param number $left
	 * @param number $right
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function add($left, $right, int $scale = self::SCALE) : string
	{
		return bcadd(strval($left), strval($right), $scale);
	}

	/**
	 * Subtracts two numbers.
	 *
	 * @param number $left
	 * @param number $right
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function sub($left, $right, int $scale = self::SCALE) : string
	{
		return bcsub(strval($left), strval($right), $scale);
	}

	/**
	 * Multiplies two numbers.
	 *
	 * @param number $left
	 * @param number $right
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function mul($left, $right, int $scale = self::SCALE) : string
	{
		return bcmul(strval($left), strval($right), $scale);
	}

	/**
	 * Divides two numbers.
	 *
	 * @param number $left
	 * @param number $right
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function div($left, $right, int $scale = self::SCALE) : string
	{
		return bcdiv(strval($left), strval($right), $scale);
	}

	/**
	 * Raises a number to a power.
	 *
	 * @param number $left
	 * @param number $right
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function pow($left, $right, int $scale = self::SCALE) : string
	{
		return bcpow(strval($left), strval($right), $scale);
	}

	/**
	 * Returns the square root of a number.
	 *
	 * @param number $operand
	 * @param int $scale [Optional] Default = Math::SCALE
	 *
	 * @return string
	 */
	public static function sqrt($operand, int $scale = self::SCALE) : string
	{
		return bcsqrt(strval($operand), $scale);
	}

	/**
	 * Sums numeric values in an array.
	 *
	 * @param array $array
	 * @param int $count [Optional]
	 *
	 * @return string
	 */
	public static function arraySum(array $array, &$count = null) : string
	{
		$sum = '0';
		$count = '0';

		foreach(self::filter($array) as $value) {
			$sum = self::add($sum, $value);
			$count = self::add($count, '1');
		}

		return $sum;
	}

	/**
	 * Calculate mean (simple arithmetic average).
	 *
	 * @param array $values
	 *
	 * @return string Mean of the numbers in $values
	 */
	public static function mean(array $values) : string
	{
		$sum = self::arraySum($values, $n);

		return self::div($sum, $n);
	}

	/**
	 * Calculate median.
	 *
	 * @param array $values
	 *
	 * @return string Median value
	 */
	public static function median(array $values)
	{
		$values = array_values(array_map('strval', $values));
		sort($values, SORT_NUMERIC);
		$n = count($values);
		// exact median
		if (isset($values[$n/2])) {
			return $values[$n/2];
		}
		// average of two middle values
		$m1 = ($n-1)/2;
		$m2 = ($n+1)/2;
		if (isset($values[$m1]) && isset($values[$m2])) {
			return self::div(self::add($values[$m1], $values[$m2]), '2');
		}
		// best guess
		$mrnd = (int) round($n/2, 0);
		if (isset($values[$mrnd])) {
			return $values[$mrnd];
		}
		return null;
	}

	/**
	 * Calculate the sum of products.
	 *
	 * @param array $x_values
	 * @param array $y_values
	 *
	 * @return string Sum of products.
	 */
	public static function sumxy(array $x_values, array $y_values)
	{
		$sum = '0';

		foreach($x_values as $i => $x) {

			if (isset($y_values[$i])) {

				$y = $y_values[$i];

				$sum = self::add(
					$sum,
					self::mul($x, $y)
				);
			}
		}

		return (string)$sum;
	}

	/**
	 * Compute the sum of squares.
	 *
	 * @param array 			$values 	An array of values.
	 * @param null|scalar|array $values2	Various formats:
	 * 		- If null, squares each value in $values.
	 * 		- If scalar, squares the difference between each value in $values and
	 * 		  	the $values2 (good for explained/regression SS).
	 * 		- If array, squares the difference between betweeen each value in $values
	 * 			and the corresponding value in $values2 with the same index (good for residual SS).
	 *
	 * @return string Sum of the squares
	 */
	public static function sos(array $values, $values2 = null) : string
	{
		$values = self::filter($values);
		$square_self = true;

		if (isset($values2)) {

			$square_self = false;

			if (is_numeric($values2)) {
				// Create an array with the keys from $values where each value = $values2
				$values2 = array_fill_keys(array_keys($values), $values2);
			} else if (is_array($values2)) {
				$values2 = self::filter($values2);
			} else {
				throw new \Exception();
			}
		}

		$sum = '0';

		foreach ($values as $i => $val) {

			if ($square_self) {
				$value = $val;
			} else {
				if (! isset($values2[$i])) {
					continue;
				}
				$value = self::sub($val, $values2[$i]);
			}

			$sum = self::add($sum, self::pow($value, '2'));
		}

		return (string)$sum;
	}

	/**
	 * Calculate variance.
	 *
	 * Calculation [sample]:  	 SumOfSquares(values, Mean(values)) / (Count(values) - 1)
	 * Calculation [population]: Covariance(values, values)
	 *
	 * @param array $values
	 * @param bool $is_sample [Optional] Default = false.
	 *
	 * @return string Variance of the values.
	 */
	public static function variance(array $values, bool $is_sample = false) : string
	{
		$values = self::filter($values);

		if ($is_sample) {
			// = SOS(r) / (COUNT(s) - 1)
			return self::div(
				self::sos($values, self::mean($values)),
				self::sub(count($values), 1)
			);
		}

		return self::covariance($values, $values);
	}

	/**
	 * Compute standard deviation.
	 *
	 * Calculation:  SquareRoot(Variance(values))
	 *
	 * @param array $values		The array of data to find the standard deviation for.
	 * 							Note that all values of the array will be cast to float.
	 * @param bool 	$is_sample 	[Optional] Indicates if $values represents a sample of the
	 * 							population (otherwise its the population); Default = false.
	 *
	 * @return string|bool The standard deviation or false on error.
	 */
	public static function stddev(array $values, bool $is_sample = false)
	{
		$values = self::filter($values);

		if (count($values) < 2) {
			trigger_error("The array has too few elements", E_USER_NOTICE);
			return false;
		}

		return self::sqrt(self::variance($values, $is_sample));
	}

	/**
	 * Calculate covariance.
	 *
	 * Calculation:  [SumOfProducts(x,y) / Count(x)] - [Mean(x) * Mean(y)]
	 *
	 * @param array $x_values Dependent variable values.
	 * @param array $y_values Independent variable values.
	 *
	 * @return string Covariance of x and y.
	 */
	public static function covariance(array $x_values, array $y_values)
	{
		$left = self::div(self::sumxy($x_values, $y_values), self::count($x_values));
		$right = self::mul(self::mean($x_values), self::mean($y_values));

		return self::sub($left, $right);
	}

	/**
	 * Compute correlation.
	 *
	 * Divides the covariance of x and y by the product of their sample standard deviations
	 *
	 * @param array $x_values
	 * @param array $y_values
	 *
	 * @return string Correlation of x and y
	 */
	public static function correlation(array $x_values, array $y_values)
	{
		$sdxy = self::mul(self::stddev($x_values, true), self::stddev($y_values, true));

		return self::div(self::covariance($x_values, $y_values), $sdxy);
	}

	/**
	 * Returns the present value of a cashflow.
	 *
	 * @param int|float|string 	$cashflow 	Numeric quantity of currency.
	 * @param float|string 		$rate 		Discount rate
	 * @param int|float|string 	$period 	A number representing time period in which the
	 * 										cash flow occurs (e.g. for an annual cashflow,
	 * 										start at 0 and increase by 1 each year).
	 *
	 * @return string Present value of the cash flow.
	 */
	public static function pv($cashflow, $rate, $period = 0)
	{
		if ($period < 1) {
			return (string)$cashflow;
		}

		return self::div(
			$cashflow,
			self::pow(self::add($rate, '1'), $period)
		);
	}

	/**
	 * Returns the Net Present Value of a series of cashflows.
	 *
	 * @param array $cashflows Indexed array of cash flows.
	 * @param number $rate Discount rate applied.
	 *
	 * @return string NPV of $cashflows discounted at $rate.
	 */
	public static function npv(array $cashflows, $rate)
	{
		$npv = "0.0";

		foreach ($cashflows as $index => $cashflow) {
			$npv = self::add($npv, self::pv($cashflow, $rate, $index));
		}

		return (string)$npv;
	}

	/**
	 * Returns the weighted average of a series of values.
	 *
	 * @param array $values Indexed array of values.
	 * @param array $weights Indexed array of weights corresponding to each value.
	 * @return string Weighted average of values.
	 */
	public static function weightedAvg(array $values, array $weights) {

		if (count($values) !== count($weights)) {
			trigger_error("Must pass the same number of weights and values.");
			return null;
		}

		$weighted_sum = "0.0";

		foreach ($values as $i => $val) {
			$weighted_sum = self::add($weighted_sum, self::mul($val, $weights[$i]));
		}

		return self::div($weighted_sum, array_sum($weights));
	}

	/** ========================================
	  * Percentages
	  * ===================================== */

	/**
	 * Returns the % of an amount of the total.
	 *
	 * e.g. for operating margin, use operating income as 1st arg, revenue as 2nd.
	 * e.g. for capex as a % of sales, use capex as 1st arg, revenue as 2nd.
	 *
	 * @param number $portion An amount, a portion of the total.
	 * @param number $total The total amount.
	 * @return string %
	 */
	public static function pct($portion, $total) {
		return self::div($portion, $total);
	}

	/**
	 * Returns the % change between two values.
	 *
	 * @param number $current The current value.
	 * @param number $previous The previous value.
	 * @return string Percent change from previous to current.
	 */
	public static function pctChange($current, $previous) {
		return self::div(self::sub($current, $previous), $previous);
	}

	/**
	 * Convert an array of values to % change.
	 *
	 * @param array $values Raw values ordered from oldest to newest.
	 * @return array Array of the % change between values.
	 */
	public static function pctChangeArray(array $values) {
		$pcts = array();
		$keys = array_keys($values);
		$vals = array_values($values);
		foreach ($vals as $i => $value) {
			if (0 !== $i) {
				$prev = $vals[$i-1];
				if (0 == $prev) {
					$pcts[$i] = '0';
				} else {
					$pcts[$i] = (string)self::div(self::sub($value, $prev), $prev);
				}
			}
		}
		array_shift($keys);
		return array_combine($keys, $pcts);
	}

}
