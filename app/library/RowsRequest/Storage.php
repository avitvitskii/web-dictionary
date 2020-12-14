<?php

namespace Core\RowsRequest;

use Core\Cls\Configurable;
use Core\RowsRequest;

/**
 * Class RowsRequest Storage
 *
 * @package Core\RowsRequest
 */
abstract class Storage
{
	use Configurable;

	/**
	 * Default adapter names
	 */
	const ADAPTER_SESSION = 'Core\RowsRequest\Storage\Session';
	const ADAPTER_BLACK_HOLE = 'Core\RowsRequest\Storage\BlackHole';

	/**
	 * Save request data to storage
	 *
	 * @param RowsRequest $request
	 * @return Storage
	 */
	abstract public function save(RowsRequest $request) :Storage;

	/**
	 * Load data from storage to new or existing request instance
	 *
	 * @param RowsRequest $request
	 * @return Storage
	 */
	abstract public function load(RowsRequest $request) :Storage;

	/**
	 * Clear request storage
	 *
	 * @return Storage
	 */
	abstract public function clear() :Storage;
}