<?php

namespace Core;

/**
 * File process Class
 */
class File
{
	/**
	 * Temp paths to delete on shutdown
	 * @var array
	 */
	protected static $_tempPaths = [];

	/**
	 * Remove temp paths callback registered flag
	 * @var bool
	 */
	protected static $_tempPathShutdownRegistered = false;

	/**
	 * Delete all contents in directory
	 *
	 * @param string $dir
	 * @param boolean $removeDir
	 * @return bool
	 */
	public static function clearDir(string $dir, bool $removeDir = true) :bool
	{
		if (empty($dir) || !is_dir($dir)) {
			return false;
		}

		$files = scandir($dir);

		foreach ($files as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			$file = $dir . DIRECTORY_SEPARATOR . $file;
			if (is_dir($file)) {
				self::clearDir($file);
				if (is_dir($file)) {
					rmdir($file);
				}
			} else {
				unlink($file);
			}
		}

		return ($removeDir ? rmdir($dir) : true);
	}

	/**
	 * Copy directory contents
	 *
	 * @param  string  $srcDir  Full path to source directory
	 * @param  string  $destDir Full path to destination folder
	 * @param  boolean $recursive
	 * @return boolean
	 */
	public static function copyDirContents(string $srcDir, string $destDir, bool $recursive = true) :bool
	{
		if (empty($srcDir) || empty($destDir)) {
			return false;
		}

		if (!($srcDir = realpath($srcDir))) {
			return false;
		}

		if (!is_dir($srcDir)) {
			return false;
		}

		if (!is_dir($destDir)) {
			if (file_exists($destDir)) {
				return false;
			}
			if (!mkdir($destDir, 0755, true)) {
				return false;
			}
			$destDir = realpath($destDir);
		}

		$destDir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $destDir);
		$destDir = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$srcDir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $srcDir);
		$srcDir = rtrim($srcDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		$srcFiles = scandir($srcDir);

		foreach ($srcFiles as $file) {
			if ($file == '.' || $file == '..') {
				continue;
			}

			if (is_dir($srcDir . $file)) {
				if ($recursive && !self::copyDirContents($srcDir . $file, $destDir . $file)) {
					return false;
				}
			} else {
				if (!copy($srcDir . $file, $destDir . $file)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check if path exists and it is not a dir
	 *
	 * @param string $path
	 * @param bool $throwException
	 * @return bool
	 * @throws Exception
	 */
	public static function isFile(string $path, bool $throwException = false) :bool
	{
		if (!file_exists($path)) {
			if ($throwException) {
				throw new Exception('File ' . $path . ' does not exist');
			}
			return false;
		}

		if (is_dir($path)) {
			if ($throwException) {
				throw new Exception($path . ' is a directory');
			}
			return false;
		}

		return true;
	}

	/**
	 * Check if path is an existing directory
	 *
	 * @param string $path
	 * @param bool $throwException
	 * @return bool
	 * @throws Exception
	 */
	public static function isDir(string $path, bool $throwException = false) :bool
	{
		$res = is_dir($path);

		if ($res || !$throwException) {
			return $res;
		}

		if (file_exists($path)) {
			throw new Exception($path . ' is a file');
		}

		throw new Exception('Directory ' . $path . ' does not exist');
	}

	/**
	 * Get file MIME type
	 *
	 * @param string $path
	 * @param bool $strict
	 * @param bool $categoryOnly
	 * @return string|null
	 * @throws Exception
	 */
	public static function getMime(string $path, bool $strict = false, bool $categoryOnly = false) :?string
	{
		if (!file_exists($path)) {
			throw new Exception('File ' . $path . ' does not exist');
		}

		$info = finfo_open(FILEINFO_MIME_TYPE);
		if (!($mime = finfo_file($info, $path))) {
			if ($strict) {
				throw new Exception('Cannot get file MIME type for ' . $path);
			}
			$mime = null;
		}

		if ($mime !== null && $categoryOnly) {
			$mime = explode('/', $mime);
			$mime = reset($mime);
		}

		return $mime;
	}

	/**
	 * Returns file extension from name / path
	 *
	 * @param  string  $file
	 * @param  boolean $lowercase
	 * @return string
	 */
	public static function getExt($file = '', $lowercase = false)
	{
		if (empty($file)) {
			return '';
		}

		$file = basename($file);

		$ext = explode('.', $file);
		if (count($ext) < 2) {
			return '';
		}

		$ext = end($ext);

		return (!$lowercase ? $ext : strtolower($ext));
	}

	/**
	 * Return file name without extension
	 *
	 * @param string $file
	 * @return string
	 */
	public static function removeExt($file)
	{
		if (!($ext = static::getExt($file))) {
			return $file;
		}

		return substr($file, 0, strlen('.' . $ext) * (-1));
	}

	/**
	 * Create zip-archive with given files
	 *
	 * @param  string|array $files
	 * @param  string $path Path to destination zip
	 * @return string Path to archive
	 * @throws Exception
	 */
	public static function zipFiles($files, $path)
	{
		if (empty($files)) {
			throw new Exception('Empty file(s) given');
		}

		if (empty($path)) {
			throw new Exception('Empty archive path given');
		}

		if (!is_array($files)) {
			$files = array($files);
		}

		foreach ($files as $file) {
			if (!file_exists($file)) {
				throw new Exception('File ' . $file . ' does not exist');
			}
		}

		$zip = new \ZipArchive();

		$zip->open($path, \ZIPARCHIVE::CREATE);

		foreach ($files as $file) {
			$zip->addFile($file, basename($file));
		}

		$zip->close();

		return $path;
	}

	/**
	 * Create zip-archive from folder
	 *
	 * @param  string $zipName Zip file basename (without extension)
	 * @param  string $path Path to source directory
	 * @param bool $saveDirStructure
	 * @return string Path to archive
	 * @internal param bool $subpath
	 */
	public static function zipFolder($zipName, $path, $saveDirStructure = false)
	{
		$zipName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName . '.zip';

		return self::zipFolderToPath($path, $zipName, $saveDirStructure);
	}

	/**
	 * Zip folder to specified $targetPath
	 *
	 * @param string $sourcePath
	 * @param string $targetPath
	 * @param bool $keepDirsStructure
	 * @return string
	 * @throws Exception
	 */
	public static function zipFolderToPath(string $sourcePath, string $targetPath, $keepDirsStructure = true) :string
	{
		if (!is_dir($sourcePath)) {
			throw new Exception($sourcePath . ' is not a valid directory path');
		}
		$sourcePath = realpath($sourcePath);

		if (is_dir($targetPath)) {
			$targetPath .= basename($sourcePath) . '.zip';
		}

		if (file_exists($targetPath)) {
			unlink($targetPath);
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$sourcePath,
				\RecursiveDirectoryIterator::SKIP_DOTS
			),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$zip = new \ZipArchive();

		$zip->open($targetPath, \ZIPARCHIVE::CREATE);

		foreach ($iterator as $item) {
			/* @var \DirectoryIterator $item */
			if (!$item->isDir()) {
				$name = (
				$keepDirsStructure ?
					str_replace($sourcePath . DIRECTORY_SEPARATOR, '', $item->getRealPath()) :
					basename($item->getRealPath())
				);
				$zip->addFile($item->getRealPath(), $name);
			}
		}

		$zip->close();

		$targetPath = realpath($targetPath);

		return $targetPath;
	}

	/**
	 * Delete expired files in $path by $lifetime (in seconds)
	 *
	 * @param  string $path
	 * @param  int $lifetime
	 * @return array
	 * @throws Exception
	 */
	public static function deleteExpired($path, $lifetime)
	{
		if (!is_dir($path)) {
			throw new Exception('Path ' . $path . ' does not exist or is not a directory');
		}

		$filesRemoved = array();

		$files = scandir($path);

		foreach ($files as $file) {
			if (is_dir($path . $file)) {
				continue;
			}

			if (static::deleteIfExpired($path . $file, $lifetime)) {
				$filesRemoved[] = $path . $file;
			}
		}

		return $filesRemoved;
	}

	/**
	 * Delete expired file by lifetime (in seconds)
	 *
	 * @param  string $file
	 * @param  int $lifetime
	 * @param  bool $silent
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteIfExpired($file, $lifetime, $silent = false)
	{
		if (!file_exists($file)) {
			if ($silent) {
				return false;
			}
			throw new Exception('File does not exist');
		}

		if ((time() - filemtime($file)) > (int)$lifetime) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Register temporary directory
	 * or file which will be deleted on shutdown.
	 * It is highly recommended to use open_basedir directive
	 * to prevent possible removing of good files
	 *
	 * @param $path
	 * @return bool
	 */
	public static function registerTemp($path)
	{
		if (empty($path) || !is_string($path)) {
			return false;
		}

		if (!in_array($path, self::$_tempPaths)) {
			self::$_tempPaths[] = $path;
		}

		if (!self::$_tempPathShutdownRegistered) {
			register_shutdown_function(array(get_called_class(), 'deleteRegisteredTemp'));
			self::$_tempPathShutdownRegistered = true;
		}

		return true;
	}

	/**
	 * Delete registered temporary dirs & files
	 *
	 * @return int
	 */
	public static function deleteRegisteredTemp()
	{
		if (empty(self::$_tempPaths)) {
			return 0;
		}

		$count = 0;

		// Using array_pop to improve huge array speed
		while (($path = array_pop(self::$_tempPaths)) !== null) {
			if (!file_exists($path)) {
				continue;
			}
			if (is_dir($path)) {
				if (self::clearDir($path, true)) {
					$count++;
				}
			} else {
				if (unlink($path)) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Check directory has no children items.
	 * If $recursive = TRUE, all children directories
	 * will be checked and if all of them are empty, TRUE will be returned.
	 * If $silent = TRUE, exception won't be thrown
	 *
	 * @param  string $path
	 * @param  bool $recursive
	 * @param  bool $silent
	 * @return bool|null
	 * @throws Exception
	 */
	public static function dirIsEmpty($path, $recursive = false, $silent = false)
	{
		if (!is_dir($path) || !is_readable($path)) {
			if ($silent) {
				return null;
			}
			throw new Exception('Directory ' . $path . ' does not exist or is not readable');
		}

		$path = realpath($path) . DIRECTORY_SEPARATOR;
		$dir  = opendir($path);

		while (($item = readdir($dir)) !== false) {
			if ($item != '.' && $item != '..') {
				if (!$recursive) {
					closedir($dir);
					return false;
				}

				if (is_dir($path . $item) && !static::dirIsEmpty($path . $item, $recursive, $silent)) {
					closedir($dir);
					return false;
				}
			}
		}

		closedir($dir);

		return true;
	}

	/**
	 * Set download file headers
	 *
	 * @param string $fileName
	 * @param int $fileSize
	 */
	public static function setDownloadHeaders($fileName = null, $fileSize = null)
	{
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment' . ($fileName ? '; filename=' . $fileName : ''));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');

		if ($fileSize) {
			header('Content-Length: ' . $fileSize);
		}
	}

	/**
	 * Send download file response
	 *
	 * @param string $file
	 * @param string $name
	 * @throws Exception
	 */
	public static function download($file, string $name = null)
	{
		if (!file_exists($file)) {
			throw new Exception('File ' . $file . ' does not exist');
		}
		if (is_dir($file)) {
			throw new Exception($file . ' is a directory');
		}

		if (ob_get_level()) {
			ob_end_clean();
		}

		if ($name !== null) {
			$name = trim(strip_tags($name));
			$name = str_replace(array('/', '\\'), '', $name);
			if ($name === '') {
				$name = basename($file);
			}
		} else {
			$name = basename($file);
		}

		self::setDownloadHeaders($name, filesize($file));

		readfile($file);

		exit;
	}

	/**
	 * Download string content as file response
	 *
	 * @param string $content
	 * @param string $fileName
	 * @throws Exception
	 */
	public static function downloadContent($content, $fileName = null)
	{
		if (!is_string($content)) {
			throw new Exception('Content is not a string');
		}

		if (ob_get_level()) {
			ob_end_clean();
		}

		self::setDownloadHeaders($fileName, strlen($content));

		echo $content;

		exit;
	}

	/**
	 * Send file content to output
	 *
	 * @param string $file
	 * @param bool $cacheAllowed
	 * @throws Exception
	 */
	public static function output($file, $cacheAllowed = false)
	{
		if (!file_exists($file)) {
			throw new Exception('File ' . $file . ' does not exist');
		}
		if (is_dir($file)) {
			throw new Exception($file . ' is a directory');
		}

		if (ob_get_level()) {
			ob_end_clean();
		}

		$info = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($info, $file);

		header('Content-Type: ' . $mime);
		header('Content-Length: ' . filesize($file));

		if (!$cacheAllowed) {
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
		}

		readfile($file);

		exit;
	}

	/**
	 * Send string content as file output
	 *
	 * @param $content
	 * @param string $mime
	 * @param bool $cacheAllowed
	 * @throws Exception
	 */
	public static function outputContent($content, $mime = null, $cacheAllowed = false)
	{
		if (!is_string($content)) {
			throw new Exception('Content is not a string');
		}

		if (ob_get_level()) {
			ob_end_clean();
		}

		if ($mime) {
			header('Content-Type: ' . $mime);
		}
		header('Content-Length: ' . strlen($content));

		if (!$cacheAllowed) {
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
		}

		echo $content;

		exit;
	}

	/**
	 * Register temp file & send it to download
	 *
	 * @param string $file
	 * @param string $name
	 * @throws Exception
	 */
	public static function downloadTemp($file, string $name = null)
	{
		if (!file_exists($file)) {
			throw new Exception('File ' . $file . ' does not exist');
		}
		if (is_dir($file)) {
			throw new Exception($file . ' is a directory');
		}

		static::registerTemp($file);
		static::download($file, $name);
	}

	/**
	 * Register temp file & send it to download
	 *
	 * @param string $file
	 * @param bool $cacheAllowed
	 * @throws Exception
	 */
	public static function outputTemp($file, $cacheAllowed = false)
	{
		if (!file_exists($file)) {
			throw new Exception('File ' . $file . ' does not exist');
		}
		if (is_dir($file)) {
			throw new Exception($file . ' is a directory');
		}

		static::registerTemp($file);
		static::output($file, $cacheAllowed);
	}

	/**
	 * Get new file path in $dirPath with random name.
	 *
	 * @param string|null $dirPath
	 * @param string|null $prefix
	 * @param string|null $ext
	 * @return string
	 * @throws Exception
	 * @throws \Exception
	 */
	public static function randomName(string $dirPath = null, string $prefix = null, string $ext = null)
	{
		if ($dirPath === null) {
			$dirPath = sys_get_temp_dir();
		}
		$dirPath = rtrim($dirPath, '/\\') . DIRECTORY_SEPARATOR;
		self::isDir($dirPath, true);

		$hash = random_bytes(16);
		$hash = bin2hex($hash);
		$len = 5;
		$max = strlen($hash);

		if ((string)$ext !== '') {
			$ext = '.' . $ext;
		}

		while (true) {
			$file = $dirPath . $prefix . substr($hash, 0, $len) . $ext;
			if (!file_exists($file)) {
				return $file;
			}
			if ($len >= $max) {
				break;
			}
			$len++;
		}

		throw new Exception('Failed to allocate temp file in ' . $dirPath . ' directory');
	}

	/**
	 * Get new file path in $dirPath with random name
	 * and register is as temporary file.
	 *
	 * @param string|null $dirPath
	 * @param string|null $prefix
	 * @param string|null $ext
	 * @return string
	 * @throws Exception
	 * @throws \Exception
	 */
	public static function tempFile(string $dirPath = null, string $prefix = null, string $ext = null)
	{
		$file = self::randomName($dirPath, $prefix, $ext);

		self::registerTemp($file);

		return $file;
	}
}