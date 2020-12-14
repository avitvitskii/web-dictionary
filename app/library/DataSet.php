<?php

namespace Core;

class DataSet
{
	/**
	 * Collect values from $column in data multi-array
	 *
	 * @param  array $rows
	 * @param  string $column (Optional)
	 * @param  bool $applyIdsFilter Apply IDs filter (Unsigned Integer > 0 value)
	 * @param  bool $combine Combine to array($value => $value)
	 * @return array
	 */
	public static function collectColumn(array $rows, string $column = null, bool $applyIdsFilter = false, bool $combine = null) :array
	{
		if (empty($rows)) {
			return array();
		}

		if ($combine === null) {
			$combine = $applyIdsFilter;
		}

		$values = array();
		foreach ($rows as $row) {
			if (!is_array($row) || empty($row)) {
				continue;
			}
			if ($column !== null) {
				if (!isset($row[$column])) {
					continue;
				}
				$value = $row[$column];
			} else {
				$value = reset($row);
			}
			if ($applyIdsFilter) {
				if (($value = (int)$value) <= 0) {
					continue;
				}
			}
			if ($combine) {
				if (!is_scalar($value) || is_bool($value)) {
					continue;
				}
				$values[$value] = $value;
			} else {
				$values[] = $value;
			}
		}

		return $values;
	}

	/**
	 * Get key => value pairs array from data set
	 *
	 * @param array $rows
	 * @param string|null $keysColumn   First column by default
	 * @param string|null $valuesColumn Second (or first if not found) column by default
	 * @return array
	 */
	public static function getPairs(array $rows, string $keysColumn = null, string $valuesColumn = null) :array
	{
		$res = array();

		foreach ($rows as $row) {
			if (empty($row) || !is_array($row)) {
				continue;
			}
			$keys = array_keys($row);
			if ($keysColumn === null) {
				$keysColumn = $keys[0];
			}
			if ($valuesColumn === null) {
				$valuesColumn = (isset($keys[1]) ? $keys[1] : $keysColumn);
			}
			if (!in_array($keysColumn, $keys)) {
				continue;
			}
			$key = $row[$keysColumn];
			if (!is_string($key) && !is_numeric($key)) {
				throw new Exception('Cannot use ' . gettype($key) . ' value as an array key');
			}
			$res[$key] = (isset($row[$valuesColumn]) ? $row[$valuesColumn] : null);
		}

		return $res;
	}

	/**
	 * Index rows with specified column
	 *
	 * @param  array $rows
	 * @param  string $column
	 * @param  bool $applyIdsFilter
	 * @return array
	 * @throws Exception
	 */
	public static function index(array $rows, string $column = null, bool $applyIdsFilter = false) :array
	{
		if (empty($rows)) {
			return array();
		}

		$res  = array();

		foreach ($rows as $key => $row) {
			unset($rows[$key]);
			if (!is_array($row) || empty($row)) {
				continue;
			}
			if ($column !== null) {
				if (!isset($row[$column])) {
					continue;
				}
				$value = $row[$column];
			} else {
				$value = reset($row);
			}
			if ($applyIdsFilter) {
				if (($value = (int)$value) <= 0) {
					continue;
				}
			}
			if (!is_scalar($value) || is_bool($value)) {
				continue;
			}
			$res[$value] = $row;
		}

		return $res;
	}

	/**
	 * Sort columns in multi-array by specified columns list
	 *
	 * @param  array $rows
	 * @param  array $columnsOrder
	 * @param  bool  $skipUnknownColumns
	 * @return array
	 */
	public static function sortColumns(array $rows, array $columnsOrder, bool $skipUnknownColumns = false) :array
	{
		if (empty($rows) || empty($columnsOrder) || !is_array($columnsOrder)) {
			return $rows;
		}

		foreach ($rows as $key => $row) {
			if (!is_array($row)) {
				continue;
			}

			$resRow  = array();
			$rowKeys = array_keys($row);

			foreach ($columnsOrder as $col) {
				if (in_array($col, $rowKeys, true)) {
					$resRow[$col] = $row[$col];
				}
			}

			if (!$skipUnknownColumns) {
				$missedCols = array_diff(array_keys($row), $columnsOrder);
				foreach ($missedCols as $col) {
					$resRow[$col] = $row[$col];
				}
			}

			$rows[$key] = $resRow;
		}

		return $rows;
	}

	/**
	 * Prepend dataset header as first row of array
	 *
	 * @param array $rows
	 * @param bool $beautify
	 * @return array
	 */
	public static function prependWithNames(array $rows, bool $beautify = false) :array
	{
		if (empty($rows)) {
			return $rows;
		}

		$names = reset($rows);
		if (!is_array($rows)) {
			return $rows;
		}

		$names = array_keys($names);
		$names = array_combine($names, $names);

		if ($beautify) {
			$names = static::beautifyNames($names);
		}

		array_unshift($rows, $names);

		return $rows;
	}

	/**
	 * Use first row of data set as rows items keys
	 *
	 * @param array $rows
	 * @param bool $keepUnknown
	 * @return array
	 * @throws Exception
	 */
	public static function readNamesFromFirstRow(array $rows, $keepUnknown = false) :array
	{
		if (empty($rows)) {
			return array();
		}

		$header = array_shift($rows);

		if (!is_array($header)) {
			throw new Exception('Specified array is not a valid data set');
		}

		return self::rowsApplyNames($rows, $header, $keepUnknown);
	}

	/**
	 * Beautify header names
	 *
	 * @param array $names
	 * @return array
	 */
	public static function beautifyNames(array $names) :array
	{
		if (empty($names)) {
			return array();
		}

		foreach ($names as $index => $name) {
			$names[$index] = Str::friendlyName($name);
		}

		return $names;
	}

	/**
	 * Uglify header names
	 *
	 * @param array $names
	 * @return array
	 */
	public static function uglifyNames(array $names) :array
	{
		if (empty($names)) {
			return array();
		}

		foreach ($names as $index => $name) {
			$names[$index] = Str::unfriendlyName($name);
		}

		return $names;
	}

	/**
	 * Apply to each row keys from $names array
	 *
	 * @param array $rows
	 * @param array $names
	 * @param bool $keepUnknown
	 * @return array
	 * @throws Exception
	 */
	public static function rowsApplyNames(array $rows, array $names, bool $keepUnknown = false) :array
	{
		$names = array_values($names);

		foreach ($rows as $key => $row) {
			$row = array_values($row);
			$newRow = array();
			foreach ($row as $i => $value) {
				if (isset($names[$i])) {
					if (!is_string($names[$i]) && !is_numeric($names[$i])) {
						throw new Exception('Cannot use ' . gettype($names[$i]) . ' as array key');
					}
					$newRow[$names[$i]] = $value;
				} elseif ($keepUnknown) {
					$newRow[$i] = $value;
				}
			}
			$rows[$key] = $newRow;
		}

		return $rows;
	}

	/**
	 * Apply header names to row
	 *
	 * @param array $row
	 * @param array $names
	 * @param bool $keepUnknown
	 * @return array
	 */
	public static function rowApplyNames(array $row, array $names, bool $keepUnknown = false) :array
	{
		if (empty($row)) {
			return array();
		}

		$names = array_values($names);
		$row = array_values($row);
		$newRow = array();

		foreach ($row as $i => $value) {
			if (isset($names[$i])) {
				if (!is_string($names[$i]) && !is_numeric($names[$i])) {
					throw new Exception('Cannot use ' . gettype($names[$i]) . ' as array key');
				}
				$newRow[$names[$i]] = $value;
			} elseif ($keepUnknown) {
				$newRow[$i] = $value;
			}
		}

		return $newRow;
	}
}