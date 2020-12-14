<?php

namespace Core;

class Arr
{
	/**
	 * Sort array by list of values
	 *
	 * @param  array $array
	 * @param  array $sortValues
	 * @param  bool  $removeUnknown
	 * @param  bool  $strict
	 * @return array
	 */
	public static function sortByValues(array $array, array $sortValues, $removeUnknown = false, $strict = false) :array
	{
		if (empty($array) || !is_array($array) || !is_array($sortValues)) {
			return array();
		}

		$target = array();

		foreach ($sortValues as $value) {
			$key = array_search($value, $array, (bool)$strict);
			if ($key !== false) {
				$target[$key] = $value;
				unset($array[$key]);
			}
		}

		if (!$removeUnknown) {
			foreach ($array as $key => $value) {
				$target[$key] = $value;
			}
		}

		return $target;
	}

	/**
	 * Extend $array1 with $array2 values by keys -
	 * no matter, integer or string
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function extend(array $array1, array $array2) :array
	{
		if (!is_array($array1)) {
			$array1 = array();
		}
		if (!is_array($array2)) {
			$array2 = array();
		}

		foreach ($array2 as $key => $value) {
			$array1[$key] = $value;
		}

		return $array1;
	}

	/**
	 * Check if array is associative
	 *
	 * @param array $array
	 * @return bool
	 */
	public static function isAssoc(array $array) :bool
	{
		return (array_keys($array) !== range(0, count($array) - 1));
	}

    /**
     * Remove empty values from array
     *
     * @param array|null $array
     * @param callable|null $notEmptyFunction
     * @return array
     */
	public static function removeEmpty(array $array, callable $notEmptyFunction = null) :array
	{
	    if ($notEmptyFunction === null) {
	        $notEmptyFunction = function ($value) {
	            return ((string)$value !== '');
            };
        }

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = static::removeEmpty($value);
				if (empty($value)) {
					unset($array[$key]);
				} else {
					$array[$key] = $value;
				}
			} elseif (!$notEmptyFunction($value)) {
				unset($array[$key]);
			}
		}

		return $array;
	}

	/**
	 * Recursively convert array to StdClass object
	 *
	 * @param array $array
	 * @return \StdClass
	 */
	public static function toObject(array $array) :\StdClass
	{
		$object = (object)$array;

		foreach ($object as $key => $value) {
			if (is_array($value)) {
				$object->{$key} = static::toObject($value);
			}
		}

		return $object;
	}

	/**
	 * Convert values to string recursively
	 *
	 * @param array $array
	 * @return array
	 */
	public static function convertValuesToString(array $array) :array
	{
		foreach ($array as $key => $value) {
			$array[$key] = (!is_array($value) ? (string)$value : self::convertValuesToString($value));
		}

		return $array;
	}

	/**
	 * Clone all objects containing in array
	 *
	 * @param array $array
	 * @param bool $recursively
	 * @return array
	 */
	public static function cloneObjectsInArray(array $array, $recursively = false) :array
	{
		foreach ($array as $key => $item) {
			if (is_object($item)) {
				$array[$key] = clone $item;
			} elseif ($recursively && is_array($item)) {
				$array[$key] = self::cloneObjectsInArray($item, true);
			}
		}

		return $array;
	}

	/**
	 * Get list of array values by $keys specified
	 *
	 * @param array $haystack
	 * @param array $keys
	 * @param bool $stringifyKeys Compare keys as strings
	 * @return array
	 */
	public static function getValuesByKeys(array $haystack, $keys, $stringifyKeys = false) :array
	{
		if (is_string($keys) || is_numeric($keys)) {
			$keys = array($keys);
		} elseif (empty($keys)) {
			return array();
		}

		$map = null;
		$values = array();

		if ($stringifyKeys) {
			$map = array();
			foreach ($haystack as $key => $value) {
				$map[(string)$key] = $key;
			}
		}

		foreach ($keys as $key) {
			$sourceKey = false;
			if ($stringifyKeys) {
				if (array_key_exists((string)$key, $map)) {
					$sourceKey = $map[(string)$key];
				}
			} else {
				if (array_key_exists($key, $haystack)) {
					$sourceKey = $key;
				}
			}
			if ($sourceKey === false) {
				continue;
			}
			$values[$sourceKey] = $haystack[$sourceKey];
		}

		return $values;
	}
}