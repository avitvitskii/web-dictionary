<?php

namespace Core;

/**
 * Class HttpException
 *
 * @package Core
 */
class HttpException extends Exception
{
	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link http://php.net/manual/en/exception.construct.php
	 * @param string $message [optional] The Exception message to throw.
	 * @param int $code [optional] The Exception code.
	 * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
	 * @since 5.1.0
	 * @throws Exception
	 */
	public function __construct($message = null, $code = 500, \Exception $previous = null)
	{
		if (!Http::responseCodeIsValid($code)) {
			$code = 500;
		}

		if ($message === null) {
			$message = Http::responseCodeText($code, false);
		}

		parent::__construct($message, $code, $previous);
	}
}