<?php

namespace Core\RowsRequest;

class Pagination
{
	/**
	 * Default count per page value
	 */
	const PER_PAGE_DEFAULT = 10;

    /**
     * Enable pagination?
     * @var bool
     */
	protected $_enable = true;

	/**
	 * Current start position
	 * @var int
	 */
	protected $_start = 0;

	/**
	 * Count per page value
	 * @var int
	 */
	protected $_perPage = self::PER_PAGE_DEFAULT;

	/**
	 * Pagination constructor.
	 *
	 * @param int|null $start
	 * @param int|null $perPage
	 */
	public function __construct(int $start = null, int $perPage = null)
	{
		$this->start($start);
		$this->perPage($perPage);
	}

	/**
	 * Get or set start offset
	 *
	 * @param int|null $start
	 * @return int
	 */
	public function start(int $start = null) :int
	{
		if ($start !== null) {
			if ($start < 0) {
				$start = 0;
			}
			$this->_start = $start;
		}

		return $this->_start;
	}

	/**
	 * Get or set count per page value
	 *
	 * @param int|null $perPage
	 * @return int
	 */
	public function perPage(int $perPage = null) :int
	{
		if ($perPage !== null) {
			if ($perPage <= 0) {
				$perPage = self::PER_PAGE_DEFAULT;
			}
			$this->_perPage = $perPage;
		}

		return $this->_perPage;
	}

    /**
     * Enable?
     *
     * @param bool|null $enable
     * @return bool
     */
    public function enable(bool $enable = null) :bool
    {
        if ($enable !== null) {
            $this->_enable = $enable;
        }

        return $this->_enable;
    }

    /**
     * Calculate current page
     *
     * @return int
     */
	public function page() :int
    {
        return (int)ceil($this->start() / $this->perPage()) + 1;
    }
}