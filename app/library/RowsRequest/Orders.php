<?php

namespace Core\RowsRequest;

use Traversable;

class Orders implements \Countable, \IteratorAggregate
{
	/**
	 * List of Order items
	 * @var array
	 */
	protected $_orders = [];

	/**
	 * Orders constructor.
	 *
	 * @param array|null $orders
	 */
	public function __construct(array $orders = null)
	{
		if ($orders !== null) {
			$this->setFromArray($orders);
		}
	}

	/**
	 * Set from array.
	 * Example:
	 * [
	 *      'col1' => 'ASC',
	 *      'col2',
	 *      'col3 DESC'
	 * ]
	 *
	 * @param array $orders
	 * @return Orders
	 */
	public function setFromArray(array $orders) :self
	{
		foreach ($orders as $key => $value) {
			if (is_int($key)) {
				if ((string)$value === '') {
					continue;
				}
				$this->append(Order::fromString($value));
			} else {
				$order = new Order($key, $value);
				$this->append($order);
			}
		}

		return $this;
	}

	/**
	 * Append order
	 *
	 * @param Order $order
	 * @return Orders
	 */
	public function append(Order $order) :self
	{
		$this->_orders[] = $order;

		return $this;
	}

	/**
	 * Prepend order
	 *
	 * @param Order $order
	 * @return Orders
	 */
	public function prepend(Order $order) :self
	{
		array_unshift($this->_orders, $order);

		return $this;
	}

	/**
	 * Get array of Order instances
	 *
	 * @return array
	 */
	public function getAll() :array
	{
		return $this->_orders;
	}

	/**
	 * Order exists for specified column
	 *
	 * @param string $col
	 * @return bool
	 */
	public function has(string $col) :bool
	{
		foreach ($this->_orders as $order) {
			/**
			 * @var Order $order
			 */
			if ($order->col() === $col) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get array of string values
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		$res = [];

		foreach ($this->_orders as $order) {
			/**
			 * @var Order $order
			 */
			$res[] = $order->__toString();
		}

		return $res;
	}

	/**
	 * Get as [col => direction] arrays
	 *
	 * @return array
	 */
	public function toAssociativeArray() :array
	{
		$res = [];

		foreach ($this->_orders as $order) {
			/**
			 * @var Order $order
			 */
			$res[] = $order->toAssociativeArray();
		}

		return $res;
	}

	/**
	 * Get as comma-separated string
	 *
	 * @return string
	 */
	public function __toString() :string
	{
		return implode(', ', $this->toArray());
	}

	/**
	 * Clear orders array
	 *
	 * @return Orders
	 */
	public function clear() :self
	{
		$this->_orders = [];

		return $this;
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
		return count($this->_orders);
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
		return new \ArrayIterator($this->_orders);
	}

	/**
	 * Is empty?
	 *
	 * @return bool
	 */
	public function isEmpty() :bool
	{
		return empty($this->_orders);
	}
}