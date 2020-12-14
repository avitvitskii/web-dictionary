<?php

namespace Core;

class IO
{
	/**
	 * Read spreadsheet file to array.
	 * Requires SpreadsheetReader,
	 * @see https://github.com/nuovo/spreadsheet-reader
	 *
	 * @param string $path
	 * @param string|null $sheetName
	 * @param bool $strict
	 * @return array
	 */
	public static function readSpreadsheet(string $path, string $sheetName = null, bool $strict = false) :array
	{
		if (!file_exists($path)) {
			throw new HttpException('Файл не найден', 404);
		}
		if (is_dir($path)) {
			throw new HttpException('Это директория, не могу прочитать', 400);
		}

		try {
			$reader = new \SpreadsheetReader($path, null, File::getMime($path));
		} catch (\Exception $e) {
			error_log((string)$e);
			if ($strict) {
				throw new HttpException('Не удалось прочитать файл', 500);
			}
			return [];
		}

		if ($sheetName !== null) {
			$sheets = $reader->Sheets();
			if (!$sheets) {
				if ($strict) {
					throw new HttpException("Лист '" . $sheetName . "' не найден в файле", 400);
				}
				return [];
			}
			$sheetFound = false;
			foreach ($sheets as $index => $name) {
				if ($name === $sheetName) {
					$sheetFound = true;
					$reader->ChangeSheet($index);
					break;
				}
			}
			if (!$sheetFound) {
				if ($strict) {
					throw new HttpException("Лист '" . $sheetName . "' не найден в файле", 400);
				}
				return [];
			}
		}

		$header = null;
		$rows = [];

		foreach ($reader as $row) {
			if (!count(Arr::removeEmpty($row))) {
				continue;
			}
			if ($header === null) {
				$header = DataSet::uglifyNames($row);
				continue;
			}
			$rows[] = DataSet::rowApplyNames($row, $header);
		}

		return $rows;
	}

	/**
	 * Save XLSX Spreadsheet.
	 * $sheets must be in format:
	 * [
	 *      '<sheet1_name>' => [<rows>],
	 *      '<sheet2_name>' => [<rows>],
	 *      ...
	 * ]
	 *
	 * @param string $path
	 * @param array $sheets
	 * @return string
	 * @throws \Exception
	 */
	public static function saveSpreadsheet(string $path, array $sheets)
	{
		if (is_dir($path)) {
			$path = File::randomName($path, null, 'xlsx');
		}

		$writer = new \XLSXWriter();

		foreach ($sheets as $name => $rows) {
			$rows = DataSet::prependWithNames($rows, true);
			$writer->writeSheet($rows, $name);
		}

		$writer->writeToFile($path);

		return $path;
	}

	/**
	 * @param string $dirPath
	 * @param array|string|null $postfix
	 * @param array|string|null $prefix
	 * @param bool $caseSens
	 * @return \DirectoryIterator|null
	 */
	public static function fileFindFirst(string $dirPath, $postfix = null, $prefix = null, bool $caseSens = false) :?\DirectoryIterator
	{
		File::isDir($dirPath, true);

		$items = new \DirectoryIterator($dirPath);

		if ($postfix !== null && !is_array($postfix)) {
			$postfix = [(string)$postfix];
		}
		if ($prefix !== null && !is_array($prefix)) {
			$prefix = [(string)$prefix];
		}

		foreach ($items as $item) {
			if ($item->isDot() || $item->isDir()) {
				continue;
			}
			$name = $item->getFilename();
			if ($postfix !== null) {
				$found = false;
				foreach ($postfix as $val) {
					if (!$caseSens) {
						$val = strtolower($val);
					}
					$compare = strtolower(substr($name, strlen($val) * -1));
					if (!$caseSens) {
						$compare = strtolower($val);
					}
					if ($compare === $val) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					continue;
				}
			}
			if ($prefix !== null) {
				$found = false;
				foreach ($prefix as $val) {
					$fn = (!$caseSens ? 'stripos' : 'strpos');
					if ($fn($name, $val) === 0) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					continue;
				}
			}
			return $item;
		}

		return null;
	}
}