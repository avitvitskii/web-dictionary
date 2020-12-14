<?php

namespace Core\RowsRequest;
use \Traversable;

/**
 * Filters model
 */
class Filters implements \Countable, \IteratorAggregate
{
	/**
	 * List of the filters
	 * @var array
	 */
	protected $_filters = [];

	/**
	 * Add filters from array
	 *
	 * @param array $filters
	 * @param bool $clearFirst
	 * @return Filters
	 */
	public function setFromArray(array $filters, bool $clearFirst = false) :self
	{
		if ($clearFirst) {
			$this->clear();
		}

		foreach ($filters as $key => $filter) {
			if ($filter instanceof Filter) {
				$this->add($filter);
			} else {
				if ($key = (string)$key) {
					$this->add(new Filter($key, $filter));
				}
			}
		}

		return $this;
	}

	/**
	 * Add new filter
	 *
	 * @param Filter $filter
	 * @return Filters
	 */
	public function add(Filter $filter) :self
	{
		if (!$filter->isEmpty()) {
			$this->_filters[$filter->col()] = $filter;
		}

		return $this;
	}

	/**
	 * Remove filter by column name
	 *
	 * @param string $col
	 * @return Filters
	 */
	public function remove(string $col) :self
	{
		if (isset($this->_filters[$col])) {
			unset($this->_filters[$col]);
		}

		return $this;
	}

	/**
	 * Get filter
	 *
	 * @param string $col
	 * @return Filter|null
	 */
	public function get(string $col)
	{
		if (!$this->has($col)) {
			return null;
		}

		return $this->_filters[$col];
	}

	/**
	 * Has non empty filter for col?
	 *
	 * @param string $col
	 * @return bool
	 */
	public function has(string $col) :bool
	{
		if (!isset($this->_filters[$col])) {
			return false;
		}

		return !$this->_filters[$col]->isEmpty();
	}

	/**
	 * Clear filters array
	 *
	 * @return Filters
	 */
	public function clear() :self
	{
		$this->_filters = [];

		return $this;
	}

	/**
	 * Is filters array empty?
	 *
	 * @return bool
	 */
	public function isEmpty() :bool
	{
		$this->removeEmpty();

		return empty($this->_filters);
	}

	/**
	 * Remove empty filters
	 *
	 * @return Filters
	 */
	public function removeEmpty() :self
	{
		foreach ($this->_filters as $col => $filter) {
			/**
			 * @var Filter $filter
			 */
			if ($filter->isEmpty()) {
				$this->remove($col);
			}
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
		$this->removeEmpty();

		$res = [];

		foreach ($this->_filters as $filter) {
			/**
			 * @var Filter $filter
			 */
			$res[$filter->col()] = $filter->value();
		}

		return $res;
	}

	/**
	 * Get filters count
	 *
	 * @return int
	 */
	public function count() :int
	{
		return count($this->_filters);
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_filters);
	}
}