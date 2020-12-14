<?php

namespace Core\RowsRequest;

use Traversable;

class Columns implements \Countable, \IteratorAggregate
{
	/**
	 * Columns list
	 * @var array of Column
	 */
	protected $_columns = [];

	/**
	 * Columns constructor.
	 *
	 * @param array|null $columns
	 */
	public function __construct(array $columns = null)
	{
		if ($columns !== null) {
			$this->setFromArray($columns);
		}
	}

	/**
	 * Append columns from array
	 *
	 * @param array $columns
	 * @return Columns
	 */
	public function setFromArray(array $columns) :self
	{
		foreach ($columns as $key => $value) {
			if ($value instanceof Column) {
				$this->append($value);
			} elseif (is_int($key)) {
				$this->append(
					new Column($value)
				);
			} else {
				$this->append(
					new Column($key, $value)
				);
			}
		}

		return $this;
	}

	/**
	 * Append column to list
	 *
	 * @param Column $column
	 * @return Columns
	 */
	public function append(Column $column) :self
	{
		$this->_columns[] = $column;

		return $this;
	}

	/**
	 * Prepend column to list
	 *
	 * @param Column $column
	 * @return Columns
	 */
	public function prepend(Column $column) :self
	{
		array_unshift($this->_columns, $column);

		return $this;
	}

	/**
	 * Has column in list?
	 *
	 * @param string $columnName
	 * @return bool
	 */
	public function has(string $columnName) :bool
	{
		foreach ($this->_columns as $column) {
			/**
			 * @var Column $column
			 */
			if ($column->name() === $columnName) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is list empty?
	 *
	 * @return bool
	 */
	public function isEmpty() :bool
	{
		return empty($this->_columns);
	}

	/**
	 * Clear columns list
	 *
	 * @return Columns
	 */
	public function clear() :self
	{
		$this->_columns = [];

		return $this;
	}

	/**
	 * Get as columns names array
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		$res = [];

		foreach ($this->_columns as $column) {
			/**
			 * @var Column $column
			 */
			$res[] = $column->name();
		}

		return $res;
	}

	/**
	 * Get as [col1 => header1, ...] array
	 *
	 * @return array
	 */
	public function toAssociativeArray() :array
	{
		$res = [];

		foreach ($this->_columns as $column) {
			/**
			 * @var Column $column
			 */
			$res[$column->name()] = $column->header();
		}

		return $res;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator() :Traversable
	{
		return new \ArrayIterator($this->_columns);
	}

	/**
	 * Count elements of an object
	 *
	 * @link http://php.net/manual/en/countable.count.php
	 * @return int The custom count as an integer.
	 * </p>
	 * <p>
	 * The return value is cast to an integer.
	 * @since 5.1.0
	 */
	public function count() :int
	{
		return count($this->_columns);
	}
}