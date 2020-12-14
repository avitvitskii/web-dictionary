<?php

namespace Core;

/**
 * Class Http
 *
 * @package Core
 */
class Http
{
	/**
	 * HTTP response code
	 * if http_response_code function does not exist
	 * or custom code is set
	 * @var int
	 */
	protected static $_responseCode = null;

	/**
	 * Set HTTP response code
	 *
	 * @param  int $code
	 * @return void
	 * @throws Exception
	 */
	public static function setResponseCode(int $code) :void
	{
		if (!static::responseCodeIsValid($code)) {
			throw new Exception('Incorrect HTTP response code given (' . $code . ')');
		}

		if (function_exists('http_response_code')) {
			http_response_code($code);
			return;
		}

		static::$_responseCode = $code;

		header(static::responseCodeHeaderContent($code));
	}

	/**
	 * Set custom response code
	 *
	 * @param  int $code
	 * @param  string $text
	 * @return void
	 * @throws Exception
	 */
	public static function setCustomResponseCode(int $code, string $text = null) :void
	{
		if (!static::responseCodeIsValid($code)) {
			throw new Exception('Incorrect HTTP response code given (' . $code . ')');
		}

		static::$_responseCode = $code;

		header(
			static::getProtocol() . ' ' .
			$code . ' ' .
			($text !== null ? $text : static::responseCodeText($text, false))
		);
	}

	/**
	 * Get current response code
	 *
	 * @return int
	 */
	public static function getResponseCode() :int
	{
		if (static::$_responseCode === null) {
			if (function_exists('http_response_code')) {
				static::$_responseCode = http_response_code();
			}
		}

		return (static::$_responseCode !== null ? static::$_responseCode : 200);
	}

	/**
	 * Get HTTP response code header content
	 *
	 * @param int $code
	 * @return string
	 * @throws Exception
	 */
	public static function responseCodeHeaderContent(int $code) :string
	{
		if (!static::responseCodeIsValid($code)) {
			throw new Exception('Incorrect HTTP response code given (' . $code . ')');
		}

		return static::getProtocol() . ' ' . static::responseCodeText($code, true);
	}

	/**
	 * Get response code text
	 *
	 * @param int $code
	 * @param bool $prependWithCode
	 * @return string
	 */
	public static function responseCodeText(int $code, bool $prependWithCode = true) :string
	{
		$texts = static::responseCodesText();
		$result = ($texts[$code] ?? '');

		if ($prependWithCode) {
			$result = $code . ' ' . $result;
		}

		return $result;
	}

	/**
	 * Get list of known HTTP response codes texts
	 *
	 * @return array
	 */
	public static function responseCodesText() :array
	{
		return array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Time-out',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Large',
			415 => 'Unsupported Media Type',
			416 => 'Requested range not satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Time-out',
			505 => 'HTTP Version not supported'
		);
	}

	/**
	 * Check if specified HTTP response code is valid
	 *
	 * @param $code
	 * @return bool
	 */
	public static function responseCodeIsValid($code) :bool
	{
		return (is_int($code) && $code >= 100 && $code <= 599);
	}

	/**
	 * Check if specified response code is successful
	 *
	 * @param int $code
	 * @return bool
	 */
	public static function responseCodeIsSuccess(int $code) :bool
	{
		return static::responseCodeIsValid($code) && $code <= 399;
	}

	/**
	 * Check if specified response code isn't successful
	 *
	 * @param int $code
	 * @return bool
	 */
	public static function responseCodeIsError(int $code) :bool
	{
		return static::responseCodeIsValid($code) && $code >= 400;
	}

	/**
	 * Get current HTTP protocol
	 *
	 * @return string
	 */
	public static function getProtocol() :string
	{
		return (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
	}

	/**
	 * Convert header name to X-Capitalized-Names
	 *
	 * @param $name
	 * @return string
	 */
	public static function normalizeHeaderName(string $name) :string
	{
		$name = trim($name);
		if ($name === '') {
			return '';
		}

		$name = str_replace(array('-', '_'), ' ', (string)$name);
		$name = ucwords(strtolower($name));
		$name = str_replace(' ', '-', $name);

		return $name;
	}

	/**
	 * Get current headers added by header() function.
	 * Returns headers array in $name => $value format.
	 * If there is several headers with the same name, $value will be an array of header values.
	 * If header has no name (you never know), $name will be integer and $value will contain header content.
	 *
	 * @return array
	 */
	public static function headersList() :array
	{
		$headers = headers_list();

		$res = array();

		foreach ($headers as $header) {
			$parts = explode(':', $header);
			if (count($parts) > 1) {
				$name  = static::normalizeHeaderName(array_shift($parts));
				$value = trim(implode(':', $parts));
				if (!isset($res[$name])) {
					$res[$name] = $value;
				} else {
					if (is_array($res[$name])) {
						$res[$name] = array_merge($res[$name], array($value));
					} else {
						$res[$name] = array(
							$res[$name],
							$value
						);
					}
				}
			} else {
				$value = trim($header);
				$res[] = $value;
			}
		}

		return $res;
	}

	/**
	 * Header is set by header() function
	 *
	 * @param  string $name
	 * @return bool
	 */
	public static function headerIsSet(string $name) :bool
	{
		$headers = static::headersList();
		if (empty($headers)) {
			return false;
		}

		$name = static::normalizeHeaderName($name);

		return isset($headers[$name]);
	}

	/**
	 * Get current HTTP host name
	 *
	 * @param  bool $withRequestScheme Prepend with scheme, if isset in request
	 * @param  bool $withBasePath  Append base path
	 * @return string
	 */
	public static function getHost(bool $withRequestScheme = false, bool $withBasePath = false)
	{
		$host = (!empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : gethostname());

		if ($withRequestScheme) {
			$scheme = (!empty($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http');
			$host = $scheme . '://' . $host;
		}

		if ($withBasePath) {
		    // TODO: do something
			$host .= '/';
		}

		return $host;
	}

	/**
	 * Check if $host is in $hosts list.
	 * Set
	 *
	 * @param mixed $hosts
	 * @param string|NULL $host
	 * @return bool
	 * @throws Exception
	 */
	public static function hostInList($hosts, string $host = null)
	{
		if (is_object($hosts)) {
			if (method_exists($hosts, 'toArray')) {
				$hosts = $hosts->toArray();
			} else {
				$hosts = (array)$hosts;
			}
		} elseif (is_string($hosts)) {
			$hosts = array($hosts);
		}

		if (empty($hosts)) {
			return false;
		}

		if (!is_array($hosts)) {
			throw new Exception('Invalid hosts list specified');
		}

		if ($host === null) {
			$host = self::getHost();

		}
		$host = explode(':', $host);
		$host = reset($host);

		return in_array($host, $hosts, true);
	}

	/**
	 * Get request method
	 * Returns NULL, if method is unknown
	 *
	 * @return string
	 */
	public static function getRequestMethod()
	{
		if (!isset($_SERVER['REQUEST_METHOD'])) {
			return null;
		}

		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
}