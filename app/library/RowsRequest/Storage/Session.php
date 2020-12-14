<?php

namespace Core\RowsRequest\Storage;

use Core\RowsRequest;
use Core\RowsRequest\Storage;
use Core\SessionBag;
use Phalcon\Di;

class Session extends Storage
{
	/**
	 * Config params names
	 */
	const CONF_NAMESPACE = 'namespace';

	/**
	 * Namespace instance
	 * @var SessionBag
	 */
	protected $_sessionBag = null;

	/**
	 * Get session namespace instance
	 *
	 * @return SessionBag
	 */
	public function bag() :SessionBag
	{
		if ($this->_sessionBag === null) {
			$ns = $this->config(static::CONF_NAMESPACE, get_class($this));
			$this->_sessionBag = new SessionBag($ns);
            $this->_sessionBag->setDI(Di::getDefault());
		}

		return $this->_sessionBag;
	}

	/**
	 * Save request data to storage
	 *
	 * @param RowsRequest $request
	 * @return Storage
	 */
	public function save(RowsRequest $request) :Storage
	{
		$this->bag()->setFromArray($request->toArray());

		return $this;
	}

	/**
	 * Load data from storage to new or existing request instance
	 *
	 * @param RowsRequest|null $request
	 * @return Storage
	 */
	public function load(RowsRequest $request) :Storage
	{
		$request->setFromArray($this->bag()->toArray());

		return $this;
	}

	/**
	 * Clear request storage
	 *
	 * @return Storage
	 */
	public function clear() :Storage
	{
		$this->bag()->destroy();
		$this->_sessionBag = null;

		return $this;
	}
}