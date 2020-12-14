<?php

namespace Core\RowsRequest;

/**
 * Limit model
 */
class Limit
{
	/**
	 * Count to limit
	 * @var int
	 */
	protected $_count = 10;

	/**
	 * Offset value
	 * @var int
	 */
	protected $_offset = 0;

	/**
	 * Limit constructor.
	 *
	 * @param int|null $count
	 * @param int|null $offset
	 */
	public function __construct(int $count = null, int $offset = null)
	{
		$this->count($count);
		$this->offset($offset);
	}

	/**
	 * Get or set count value
	 *
	 * @param int|null $count
	 * @return int
	 */
	public function count(int $count = null) :int
	{
		if ($count !== null) {
			$this->_count = $count;
		}

		return $this->_count;
	}

	/**
	 * Set or get offset value
	 *
	 * @param int $offset
	 * @return int
	 */
	public function offset(int $offset = null) :int
	{
		if ($offset !== null) {
			$this->_offset = $offset;
		}

		return $this->_offset;
	}
}