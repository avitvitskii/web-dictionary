<?php

namespace Core;

use Core\Cls\DataRow;

class DataItem implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
	use DataRow;

	/**
	 * Data array
	 * @var array
	 */
	protected $_data = [];

	/**
	 * DataItem constructor.
	 *
	 * @param array|null $data
	 * @param bool $readOnly
	 */
	public function __construct(array $data = null, bool $readOnly = false)
	{
		if ($data !== null) {
			$this->setFromArray($data);
		}
		$this->readOnly($readOnly);
	}
}