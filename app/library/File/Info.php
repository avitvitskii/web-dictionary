<?php

namespace Core\File;

use Core\File;

class Info extends \SplFileInfo
{
	/**
	 * Get file MIME type
	 *
	 * @param bool $strict
	 * @param bool $categoryOnly
	 * @return null|string
	 */
	public function getMime(bool $strict = false, bool $categoryOnly = false) :?string
	{
		return File::getMime($this->getRealPath(), $strict, $categoryOnly);
	}
}