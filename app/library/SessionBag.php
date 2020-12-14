<?php

namespace Core;

use Phalcon\Session\Bag;

/**
 * Class SessionBag
 *
 * @package Core
 */
class SessionBag extends Bag
{
	/**
	 * Set data from array
	 *
	 * @param array $data
	 * @return SessionBag
	 */
	public function setFromArray(array $data) :self
	{
		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * Get as array
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		$res = [];

		foreach ($this as $key => $value) {
			$res[$key] = $value;
		}

		return $res;
	}
}