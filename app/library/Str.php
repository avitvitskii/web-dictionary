<?php

namespace Core;

class Str
{
	/**
	 * Get friendly number
	 *
	 * @param int|float $number
	 * @param string $label
	 * @return string
	 */
	public static function friendlyNumber($number, string $label = null) :string
	{
		if (!is_numeric($number)) {
			return '';
		}

		$number = (string)$number;
		$number = str_replace(',', '.', $number);

		if (strpos($number, '.')) {
			$number = explode('.', $number);
			$tmp = $number;
			$number = array_shift($tmp);
			$d = implode('', $tmp);
		}

		$res = '';
		$len = strlen($number);

		for ($i = $len - 1; $i >= 0; $i--) {
			$res = (($len - $i) % 3 == 0 ? ' ' : '') . $number[$i] . $res;
		}

		if (!empty($d)) {
			$res .= '.' . $d;
		}

		if ($label !== null) {
			$res .= ' ' . $label;
		}

		return $res;
	}

	/**
	 * Get friendly count
	 *
	 * @param  integer $count
	 * @param  boolean $returnWordOnly
	 * @param  array   $words e. g. array('товар', 'товара', 'товаров')
	 * @return string
	 */
	public static function friendlyCount(int $count, bool $returnWordOnly = false, array $words = ['item', 'items', 'items']) :string
	{
		$count = (int)$count;
		$last = $count % 10;

		if ($last == 0 || $last >= 5 && $last <= 9 || $count >= 11 && $count <= 20) {
			$res = $words[2];
		} else if ($last == 1) {
			$res = $words[0];
		} else {
			$res = $words[1];
		}

		if (!$returnWordOnly) {
			$res = $count . ' ' . $res;
		}

		return $res;
	}

	/**
	 * Friendly file size
	 *
	 * @param $bytes
	 * @param int $decimals
	 * @param bool $labelOnly
	 * @return string
	 * @see http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
	 */
	public static function friendlySize($bytes, int $decimals = 2, bool $labelOnly = false) :string
	{
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$factor = (int)floor((strlen($bytes) - 1) / 3);

		$res = @$size[$factor];
		if (!$labelOnly) {
			$res = sprintf('%.' . (int)$decimals . 'f', $bytes / pow(1024, $factor)) . ($res ? ' ' . $res : '');
		}

		return $res;
	}

	/**
	 * Beautify name string
	 *
	 * @param string $name
	 * @return string
	 */
	public static function friendlyName(string $name) :string
	{
		return ucwords(str_replace('_', ' ', $name));
	}

	/**
	 * Convert to lowercase_name
	 *
	 * @param string $name
	 * @return string
	 */
	public static function unfriendlyName(string $name) :string
	{
		$name = str_replace(' ', '_', $name);
		$name = strtolower($name);

		return $name;
	}
}