<?php

namespace Core\Db;

class Expr
{
	/**
	 * Expression string
	 * @var string
	 */
	protected $_data;

	/**
	 * Expr constructor.
	 *
	 * @param $data
	 */
	public function __construct(string $data)
	{
		$this->_data = $data;
	}

	/**
	 * Render
	 * @return string
	 */
	public function __toString()
	{
		return $this->_data;
	}
}