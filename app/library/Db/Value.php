<?php

namespace Core\Db;

use Phalcon\Security\Random;

/**
 * Core Db_Value class
 */
class Value implements \Countable
{
	/**
	 * Implode glue by default
	 */
	const GLUE_DEFAULT = ',';

	/**
	 * Value
	 * @var mixed
	 */
	protected $_value;

    /**
     * Is value set?
     * @var bool
     */
	protected $_isSet = false;

    /**
     * Value instance ID
     * @var string
     */
	protected $_id = null;

	/**
	 * Core_Db_Value constructor.
	 *
	 * @param mixed $value
	 */
	public function __construct($value = null)
	{
		if ($value !== null) {
            $this->set($value);
        }
	}

    /**
     * Get Value instance ID
     *
     * @return string
     */
	public function id() :string
    {
        if ($this->_id === null) {
            $random = new Random();
            try {
                $this->_id = 'val' . $random->hex(6);
            } catch (\Phalcon\Security\Exception $e) {
                $this->_id = 'val' . rand(1000, 90000);
            }
        }

        return $this->_id;
    }

	/**
	 * Get unprocessed value
	 *
	 * @return mixed
	 */
	public function get()
	{
		return $this->_value;
	}

	/**
	 * Set new value
	 *
	 * @param $value
	 * @return Value
	 */
	public function set($value) :Value
	{
		$this->_value = $value;
		$this->_isSet = true;

		return $this;
	}

	/**
	 * Add value to values list
	 *
	 * @param $value
	 * @return Value
	 */
	public function add($value) :Value
	{
		if (!is_array($this->_value)) {
		    if ($this->isValueSet()) {
                $this->_value = [$this->_value];
            } else {
		        $this->_value = [];
		        $this->_isSet = true;
            }
		}

		$this->_value[] = $value;

		return $this;
	}

    /**
     * Is specific $value set in values list?
     *
     * @param $value
     * @param bool $strict
     * @return bool
     */
	public function has($value, bool $strict = false) :bool
    {
        if (!$this->isValueSet()) {
            return false;
        }

        if (!is_array($this->_value)) {
            if ($strict) {
                return ($this->_value === $value);
            } else {
                return ($this->_value == $value);
            }
        }

        return in_array($value, $this->_value, $strict);
    }

    /**
     * Get string for prepared query body
     *
     * @param string $glue
     * @return string
     */
	public function getPrepared(string $glue = self::GLUE_DEFAULT) :string
	{
		$value = $this->get();

		if (!is_array($value)) {
			$value = [$value];
		}

		$res = [];
		$i = 0;

		foreach ($value as $val) {
			if (!($val instanceof Expr)) {
				$res[] = ':' . $this->id() . $i . ':';
			} else {
				$res[] = $val;
			}
			++$i;
		}

		return implode($glue, $res);
	}

    /**
     * Get value as array prepared to bind.
     * Core_Db_Expr values are ignored.
     *
     * @return array
     */
	public function bind() :array
	{
		$values = $this->get();

		if (!is_array($values)) {
			$values = [$values];
		}

		$bind = [];
		$i = 0;

		foreach ($values as $val) {
			if (!($val instanceof Expr)) {
				$bind[$this->id() . $i] = $val;
				++$i;
			}
		}

		return $bind;
	}

    /**
     * Get equal expression for prepared query
     *
     * @param string|null $valueToCompare
     * @return string
     */
	public function eqExprPrepared(string $valueToCompare = null) :string
	{
		$value = $this->get();

		if (!is_array($value)) {
			$res = '= ' . $this->getPrepared();
		} else {
			$res = 'IN (' . $this->getPrepared() . ')';
		}

		if ($valueToCompare !== null) {
			$res = $valueToCompare . ' ' . $res;
		}

		return $res;
	}

    /**
     * Get not equal expression for prepared query
     *
     * @param string|null $valueToCompare
     * @return string
     */
    public function neqExprPrepared(string $valueToCompare = null) :string
    {
        $value = $this->get();

        if (!is_array($value)) {
            $res = '<> ' . $this->getPrepared();
        } else {
            $res = 'NOT IN (' . $this->getPrepared() . ')';
        }

        if ($valueToCompare !== null) {
            $res = $valueToCompare . ' ' . $res;
        }

        return $res;
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
		$value = $this->get();

		if (!$this->isValueSet()) {
			return 0;
		}

		if (!is_array($value)) {
			return 1;
		}

		return count($value);
	}

    /**
     * Is value set?
     *
     * @return bool
     */
	public function isValueSet() :bool
    {
        return $this->_isSet;
    }

    /**
     * Get as quoted string
     *
     * @return string
     */
	public function __toString() :string
	{
		return $this->getPrepared();
	}
}