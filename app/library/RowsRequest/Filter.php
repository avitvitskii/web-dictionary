<?php

namespace Core\RowsRequest;

use Core\Db\Value;

/**
 * Filter model
 */
class Filter
{
	/**
	 * Column name
	 * @var string
	 */
	protected $_col;

	/**
	 * Filter value
	 * @var mixed
	 */
	protected $_value = null;

	/**
	 * Filter constructor.
	 *
	 * @param string $col
	 * @param null $value
	 * @throws Exception
	 */
	public function __construct(string $col, $value = null)
	{
		if ($col === '') {
			throw new Exception('Filter column name is empty or invalid');
		}

		if (!preg_match('/^[A-Za-z0-9_]+$/', $col)) {
			throw new Exception('Invalid column name given');
		}

		$this->_col = $col;

		$this->setValue($value);
	}

	/**
	 * Get column name
	 *
	 * @return string
	 */
	public function col() :string
	{
		return $this->_col;
	}

	/**
	 * Get filter value
	 *
	 * @return mixed
	 */
	public function value()
	{
		return $this->_value;
	}

	/**
	 * Set filter value
	 *
	 * @param $value
	 * @return Filter
	 */
	public function setValue($value) :self
	{
		$this->_value = $value;

		return $this;
	}

	/**
	 * Filter value is empty?
	 *
	 * @return bool
	 */
	public function isEmpty() :bool
	{
		if ($this->_value === null) {
			return true;
		}

		if (!is_array($this->_value)) {
			$value = trim($this->_value);
			return ($value === '');
		} else {
			$value = array_filter($this->_value, 'trim');
			return empty($value);
		}
	}

	/**
	 * Get as single array
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		$val = $this->value();

		return (is_array($val) ? $val : explode(',', $val));
	}

    /**
     * Get as Db Value
     *
     * @return Value
     */
	public function toDbValue() :Value
    {
        $value = new Value();

        if (!$this->isEmpty()) {
            $items = $this->toArray();
            if (count($items) === 1) {
                $value->set(reset($items));
            } else {
                $value->set($items);
            }
        }

        return $value;
    }
}