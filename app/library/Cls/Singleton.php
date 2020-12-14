<?php

namespace Core\Cls;

/**
 * Trait Singleton
 *
 * @package Core\Cls
 */
trait Singleton
{
	/**
	 * Instance
	 * @var self
	 */
	protected static $_instance = null;

	/**
	 * Get class instance
	 *
	 * @return self
	 */
	public static function getInstance() :self
	{
		if (static::$_instance === null) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}

	protected function __construct() {}
	protected function __clone() {}
}
