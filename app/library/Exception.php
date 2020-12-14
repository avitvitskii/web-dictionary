<?php

namespace Core;
use Throwable;

/**
 * Class Exception
 *
 * @package Core
 */
class Exception extends \RuntimeException
{
	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link http://php.net/manual/en/exception.construct.php
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param Throwable $previous [optional] The previous throwable used for the exception chaining.
	 * @since 5.1.0
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		$message = trim(strip_tags($message));

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get type or class if object
	 *
	 * @param $var
	 * @return string
	 */
	public static function typeOrClass($var)
	{
		return (is_object($var) ? get_class($var) : gettype($var));
	}
}