<?php

namespace Core\RowsRequest;

use Core\Str;

class Column
{
	/**
	 * Column name
	 * @var string
	 */
	protected $_name;

	/**
	 * Column header
	 * @var string
	 */
	protected $_header = null;

    /**
     * Is column name valid?
     *
     * @param string $name
     * @param bool $throw
     * @return bool
     */
	public static function validateColName(string $name, bool $throw = false) :bool
    {
        $res = (bool)preg_match('/^[\.A-Za-z0-9_]+$/', $name);

        if (!$res && $throw) {
            throw new Exception('Column name is not valid');
        }

        return $res;
    }

	/**
	 * Column constructor.
	 *
	 * @param string $name
	 * @param string|null $header
	 * @throws Exception
	 */
	public function __construct(string $name, string $header = null)
	{
		$this->name($name);
		$this->_header = $header;
	}

	/**
	 * Get or set column name
	 *
	 * @param string|null $name
	 * @return string
	 * @throws Exception
	 */
	public function name(string $name = null) :string
	{
		if ($name !== null) {
		    self::validateColName($name, true);
			$this->_name = $name;
		}

		return $this->_name;
	}

	/**
	 * Get column header
	 *
	 * @param string|null $header
	 * @return string
	 */
	public function header(string $header = null) :string
	{
		if ($this->_header !== null) {
			$this->_header = $header;
		}

		return ($this->_header ?? Str::friendlyName($this->name()));
	}

	/**
	 * Get as [name => header] array
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		return [
			$this->name() => $this->header()
		];
	}
}