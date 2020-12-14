<?php

namespace Core\RowsRequest;

/**
 * Order value model
 * @package Fp3dSummary
 */
class Order
{
	/**
	 * Directions constants
	 */
	const ASC = 'asc';
	const DESC = 'desc';

	/**
	 * Column name
	 * @var string
	 */
	protected $_col;

	/**
	 * Sort direction
	 * @var string
	 */
	protected $_direction = self::ASC;

	/**
	 * Create instance from 'colname ASC' string
	 *
	 * @param string $order
	 * @return Order
	 */
	public static function fromString(string $order) :self
	{
		$order = trim($order);
		$order = explode(' ', $order);

		$instance = new self($order[0]);

		if (isset($order[1])) {
			$instance->direction($order[1]);
		}

		return $instance;
	}

	/**
	 * Order constructor.
	 *
	 * @param string $col
	 * @param string|null $direction
	 */
	public function __construct(string $col, string $direction = null)
	{
		$this->col($col);
		$this->direction($direction);
	}

	/**
	 * Get or set column name
	 *
	 * @param string|null $col
	 * @return string
	 * @throws Exception
	 */
	public function col(string $col = null) :string
	{
		if ($col !== null) {
		    Column::validateColName($col);
			$this->_col = $col;
		}

		return $this->_col;
	}

	/**
	 * Get or set sort direction
	 *
	 * @param string|null $direction
	 * @return string
	 */
	public function direction(string $direction = null) :string
	{
		if ($direction !== null) {
			$direction = strtolower($direction);
			if (!in_array($direction, [self::ASC, self::DESC], true)) {
				$direction = self::ASC;
			}
			$this->_direction = $direction;
		}

		return $this->_direction;
	}

	/**
	 * Get as [col => direction] array
	 *
	 * @return array
	 */
	public function toAssociativeArray() :array
	{
		return [
			$this->col() => $this->direction()
		];
	}

	/**
	 * Get as string
	 *
	 * @return string
	 */
	public function __toString() :string
	{
		return $this->col() . ' ' . $this->direction();
	}
}