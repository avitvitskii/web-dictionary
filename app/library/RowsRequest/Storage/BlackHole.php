<?php

namespace Core\RowsRequest\Storage;

use Core\RowsRequest;
use Core\RowsRequest\Storage;

class BlackHole extends Storage
{
	/**
	 * Save request data to storage
	 *
	 * @param RowsRequest $request
	 * @return Storage
	 */
	public function save(RowsRequest $request) :Storage
	{
		return $this;
	}

	/**
	 * Load data from storage to new or existing request instance
	 *
	 * @param RowsRequest $request
	 * @return Storage
	 */
	public function load(RowsRequest $request) :Storage
	{
		return $this;
	}

	/**
	 * Clear request storage
	 *
	 * @return Storage
	 */
	public function clear() :Storage
	{
		return $this;
	}
}