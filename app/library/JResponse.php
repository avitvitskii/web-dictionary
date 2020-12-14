<?php

namespace Core;
use Core\Cls\DataRow;

/**
 * Class JResponse
 *
 * @package Core
 */
class JResponse implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
	use DataRow;

	/**
	 * Response statuses
	 */
	const STATUS_SUCCESS = 'success';
	const STATUS_REQUEST_ERR = 'fail';
	const STATUS_SERVER_ERR = 'error';

	/**
	 * Available statuses
	 */
	const STATUSES = [
		self::STATUS_SUCCESS,
		self::STATUS_REQUEST_ERR,
		self::STATUS_SERVER_ERR
	];

	/**
	 * Data array
	 * @var array
	 */
	protected $_data = array(
		'status' => null,
		'message' => null,
		'data' => null
	);

	/**
	 * Create response instance
	 *
	 * @param string $status
	 * @param string|null $message
	 * @param null $data
	 * @return JResponse
	 * @throws Exception
	 */
	public static function factory(string $status, string $message = null, $data = null) :self
	{
		$resp = new self();

		$resp->status = $status;
		$resp->message = $message;
		$resp->data = $data;

		return $resp;
	}

	/**
	 * Is status set to successful?
	 *
	 * @return bool
	 */
	public function isSuccess() :bool
	{
		return $this->get('status') === self::STATUS_SUCCESS;
	}

	/**
	 * Set success response data
	 *
	 * @param mixed $data
	 * @param string|null $message
	 * @return JResponse
	 */
	public function setSuccess($data = null, string $message = null) :self
	{
		$this->status = self::STATUS_SUCCESS;
		$this->message = $message;
		$this->data = $data;

		return $this;
	}

	/**
	 * Set request error response data
	 *
	 * @param string|null $message
	 * @param null $data
	 * @return JResponse
	 */
	public function setRequestErr(string $message = null, $data = null) :self
	{
		$this->status = self::STATUS_REQUEST_ERR;
		$this->message = $message;
		$this->data = $data;

		return $this;
	}

	/**
	 * Set server error response data
	 *
	 * @param string|null $message
	 * @param null $data
	 * @return JResponse
	 */
	public function setServerErr(string $message = null, $data = null) :self
	{
		$this->status = self::STATUS_SERVER_ERR;
		$this->message = $message;
		$this->data = $data;

		return $this;
	}

	/**
	 * Set error from exception
	 *
	 * @param \Exception $e
	 * @return JResponse
	 */
	public function setErrFromException(\Exception $e) :self
	{
		$message = $e->getMessage();
		$code = (int)$e->getCode();

		if ($e instanceof HttpException) {
			if (Http::responseCodeIsError($code) && $code < 500) {
				return $this->setRequestErr($message, $code);
			}
		}

		return $this->setServerErr($message, $code);
	}

	/**
	 * Set data value
	 *
	 * @param $key
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function set($key, $value)
	{
		$this->_readOnlyPrecondition();

		$this->keyIsValid($key, true);

		switch ($key) {
			case 'status':
				if (!in_array($value, self::STATUSES, true)) {
					throw new Exception('Unknown JResponse status: ' . $value);
				}
				break;
		}

		$this->_data[$key] = $value;

		return $this;
	}
}