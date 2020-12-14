<?php

namespace Core;

/**
 * Class DataStorage
 * Class to operate with data directory
 */
class DataStorage
{
	/**
	 * Default permissions to data directories
	 */
	const DEFAULT_PERMISSIONS = 0755;

	/**
	 * Path to data directory
	 * @var string
	 */
	protected $_path = null;

	/**
	 * Constructor
	 *
	 * @param  string $path (Optional)
	 * @throws Exception
	 */
	public function __construct($path = null)
	{
		if (!empty($path)) {
			$this->setPath($path);
		}
	}

	/**
	 * Set path to data directory
	 *
	 * @param  string $path
	 * @return $this
	 * @throws Exception
	 */
	public function setPath($path)
	{
		if (!is_string($path) || $path === '') {
			throw new Exception('Incorrect data directory path given');
		}

		$realpath = realpath($path);
		if (!$realpath) {
			throw new Exception('Path ' . $path . ' does not exist');
		}
		if (file_exists($path) && !is_dir($path)) {
			throw new Exception('Path ' . $path . ' is not a directory');
		}

		$this->_path = $realpath . DIRECTORY_SEPARATOR;

		return $this;
	}

	/**
	 * Get data directory path
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getPath()
	{
		if ($this->_path === null) {
			throw new Exception('Data directory path is not set');
		}

		return $this->_path;
	}

	/**
	 * Get absolute path to data storage directory or file item
	 *
	 * @param  string $itemName
	 * @return string
	 * @throws Exception
	 */
	public function getItemPath($itemName)
	{
		if (!isset($itemName)) {
			throw new Exception('Directory or file name is not set');
		}

		$itemName = str_replace(array('/', '\\'), '', $itemName);
		$itemName = trim($itemName, '.');
		if ($itemName === '') {
			throw new Exception('Incorrect data storage item name given');
		}

		return $this->_path() . $itemName;
	}

	/**
	 * Prepare and get full path of data subdirectory
	 *
	 * @param  string $dirName
	 * @param  bool $skipPrepare
	 * @param  bool $clearIfExists
	 * @return string|FALSE
	 * @throws Exception
	 */
	public function dir($dirName, $skipPrepare = false, $clearIfExists = false)
	{
		$path = $this->getItemPath($dirName) . DIRECTORY_SEPARATOR;

		if ($clearIfExists && is_dir($path)) {
			File::clearDir($path, false);
		}

		if ($skipPrepare) {
			return $path;
		}

		return $this->_prepareDir($path);
	}

	/**
	 * Get full path of file in data directory
	 *
	 * @param  string $fileName
	 * @param  bool $registerTemp
	 * @return string|FALSE
	 * @throws Exception
	 */
	public function file($fileName, $registerTemp = false)
	{
		$path = $this->getItemPath($fileName);

		if ($registerTemp) {
			File::registerTemp($path);
		}

		return $path;
	}

	/**
	 * Check if dir exists and is writable
	 * Tries to create/chmod directory
	 *
	 * @param  string $path
	 * @param  bool $throwException
	 * @param  bool $recursive
	 * @return bool
	 * @throws Exception
	 */
	public static function prepareDir($path, $throwException = false, $recursive = false)
	{
		if (!is_dir($path)) {
			if (!mkdir($path, static::DEFAULT_PERMISSIONS, $recursive)) {
				if (!$throwException) {
					return false;
				}
				throw new Exception('Data files dir ' . $path . ' does not exist and cannot be created');
			}
		} elseif (!is_writable($path)) {
			chmod($path, static::DEFAULT_PERMISSIONS);
			if (!is_writable($path)) {
				if (!$throwException) {
					return false;
				}
				throw new Exception('Data files dir ' . $path . ' is not writable');
			}
		}

		return true;
	}

	/**
	 * getPath() alias
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function _path()
	{
		return $this->getPath();
	}

	/**
	 * Check if dir exists and is writable
	 * Tries to create/chmod
	 *
	 * @param  string $path
	 * @return string
	 * @throws Exception
	 */
	protected function _prepareDir($path)
	{
		static::prepareDir($path, true);

		return $path;
	}
}