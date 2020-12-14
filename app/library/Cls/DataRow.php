<?php

namespace Core\Cls;
use Core\Exception;

/**
 * DataRow trait
 * implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
 */
trait DataRow
{
	/**
	 * Data array
	 * @var array
	 */
	// protected $_data = [];

	/**
	 * Object is read-only
	 * @var bool
	 */
	protected $_readOnly = false;

	/**
	 * Set read-only flag value
	 *
	 * @param bool $value
	 * @return $this
	 */
	public function readOnly($value = true)
	{
		$this->_readOnly = (bool)$value;

		return $this;
	}

	/**
	 * Is object in read-only mode?
	 *
	 * @return bool
	 */
	public function isReadOnly()
	{
		return (bool)$this->_readOnly;
	}

	/**
	 * Set data from array
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setFromArray(array $data)
	{
		$this->_readOnlyPrecondition();

		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * Set data value
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function set($key, $value)
	{
		$this->_readOnlyPrecondition();

		$this->keyIsValid($key, true);

		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Get data value
	 *
	 * @param $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		$this->keyIsValid($key, true);

		if (!$this->__isset($key)) {
			return $default;
		}

		return $this->_data[$key];
	}

	/**
	 * Clear data
	 *
	 * @return $this
	 */
	public function reset()
	{
		$this->_data = array();

		return $this;
	}

	/**
	 * Is data key valid?
	 *
	 * @param $key
	 * @param bool $throw Throw an exception if key is not valid
	 * @return bool
	 * @throws Exception
	 */
	public function keyIsValid($key, $throw = false)
	{
		$res = (is_string($key) || is_numeric($key));

		if (!$res && $throw) {
			throw new Exception('Invalid key given: ' . gettype($key));
		}

		return $res;
	}

	/**
	 * Get data array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * Throw an exception if object is in read-only mode
	 *
	 * @throws Exception
	 */
	protected function _readOnlyPrecondition()
	{
		if ($this->isReadOnly()) {
			throw new Exception('Object of ' . get_class($this) . ' is read-only');
		}
	}

	// region Magic Methods

	/**
	 * is triggered by calling isset() or empty() on inaccessible members.
	 *
	 * @param $key string
	 * @return bool
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
	 */
	public function __isset($key)
	{
		$this->keyIsValid($key, true);

		return array_key_exists($key, $this->_data);
	}

	/**
	 * is invoked when unset() is used on inaccessible members.
	 *
	 * @param $key string
	 * @return void
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
	 */
	public function __unset($key)
	{
		$this->_readOnlyPrecondition();

		$this->keyIsValid($key, true);

		if (isset($this->_data[$key])) {
			unset($this->_data[$key]);
		}
	}

	/**
	 * run when writing data to inaccessible members.
	 *
	 * @param $key string
	 * @param $value mixed
	 * @return void
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * is utilized for reading data from inaccessible members.
	 *
	 * @param $key string
	 * @return mixed
	 * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	// endregion Magic Methods

	// region Countable

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
	public function count()
	{
		return count($this->_data);
	}

	// endregion Countable

	// region IteratorAggregate

	/**
	 * Retrieve an external iterator
	 *
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_data);
	}

	// endregion IteratorAggregate

	// region ArrayAccess

	/**
	 * Whether a offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * Offset to set
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	// endregion ArrayAccess

	// region Serializable

	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize()
	{
		return serialize($this->_data);
	}

	/**
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized)
	{
		$this->_data = unserialize($serialized);
	}

	// endregion Serializable
}