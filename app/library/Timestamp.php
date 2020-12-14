<?php

namespace Core;

/**
 * Core Timestamp class
 */
class Timestamp
{
	/**
	 * Timestamp value
	 * @var int
	 */
	protected $_time;

	/**
	 * Core_Timestamp constructor.
	 *
	 * @param int|string|null $time
	 */
	public function __construct($time = null)
	{
		$this->set($time);
	}

	/**
	 * Gt time value
	 *
	 * @return int
	 */
	public function get() :int
	{
		return $this->_time;
	}

	/**
	 * Set new time value
	 *
	 * @param null $time
	 * @return $this
	 */
	public function set($time = null) :Timestamp
	{
		$this->_time = $this->_timestamp($time);

		return $this;
	}

    /**
     * Set to 00:00 of next day
     *
     * @return Timestamp
     */
	public function setNextDay() :Timestamp
    {
        $this->set(
            date(
                'Y-m-d',
                strtotime('next day', $this->get())
            ) . ' 00:00:00'
        );

        return $this;
    }

	/**
	 * Update time value with time diff string
	 * @see strtotime()
	 *
	 * @param string $diffString
	 * @return $this
	 */
	public function update(string $diffString) :Timestamp
	{
		$this->_time = $this->_timestamp($diffString, $this->get());

		return $this;
	}

	/**
	 * Add $seconds to time value
	 *
	 * @param int $seconds
	 * @return $this
	 */
	public function inc(int $seconds) :Timestamp
	{
		$this->_time += $seconds;

		return $this;
	}

	/**
	 * Deduct $seconds from time value
	 *
	 * @param int $seconds
	 * @return $this
	 */
	public function dec(int $seconds) :Timestamp
	{
		$this->_time -= $seconds;

		return $this;
	}

	/**
	 * Get formatted time value
	 *
	 * @param string|null $format
	 * @return string
	 */
	public function format(string $format = null) :string
	{
		if ($format === null) {
			$format = 'Y-m-d H:i:s';
		}

		$time = $this->get();

		if ($time === 0) {
			return '';
		}

		return date($format, $time);
	}

	/**
	 * Get formatted date in Russian
	 *
	 * @param bool $withYear
	 * @param bool $withTime
	 * @return string
	 */
	public function russian($withYear = false, $withTime = false) :string
	{
		$time = $this->get();

		$day = date('j', $time);
		$month = (int)date('n', $time);

		static $months = array(
			'января',
			'февраля',
			'марта',
			'апреля',
			'мая',
			'июня',
			'июля',
			'августа',
			'сентября',
			'октября',
			'ноября',
			'декабря'
		);

		$month = $months[$month - 1];

		$res = $day . ' ' . $month;

		if ($withYear) {
			$res .= ' ' . date('Y', $time) . ' года';
		}

		if ($withTime) {
			$res .= ' в ' . date('H:i:s', $time);
		}

		return $res;
	}

	/**
	 * Compare current time stamp with $time.
	 * Returns:
	 * -1 if current is less than $time;
	 * 0 if values are equal;
	 * 1 if current is bigger than $time.
	 *
	 * @param Timestamp|string|int|null $time
	 * @return int
	 */
	public function compare($time = null) :int
	{
		if (!($time instanceof Timestamp)) {
			$time = new Timestamp($time);
		}

		return ($this->get() <=> $time->get());
	}

	/**
	 * Get time formatted in default format
	 *
	 * @return string
	 */
	public function __toString() :string
	{
		return $this->format();
	}

	/**
	 * Get timestamp
	 *
	 * @param string|int|null $timeValue
	 * @param int|null $strToTimeBase
	 * @return int
	 * @throws Exception
	 */
	protected function _timestamp($timeValue = null, int $strToTimeBase = null) :int
	{
		if ($timeValue !== null) {
			if (is_numeric($timeValue)) {
				$timeValue = (int)$timeValue;
			} elseif (is_string($timeValue)) {
				if ($strToTimeBase === null) {
					$strToTimeBase = time();
				}
				$time = strtotime($timeValue, $strToTimeBase);
				if ($time === false) {
					throw new Exception("Cannot calculate time from '" . $timeValue . "'");
				}
				$timeValue = $time;
				unset($time);
			} elseif ($timeValue instanceof Timestamp) {
				$timeValue = $timeValue->get();
			} else {
				throw new Exception('Invalid timestamp: ' . gettype($timeValue));
			}
		} else {
			$timeValue = time();
		}

		return $timeValue;
	}
}