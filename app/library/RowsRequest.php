<?php

namespace Core;

use Core\Cls\Configurable;
use Core\RowsRequest\Column;
use Core\RowsRequest\Columns;
use Core\RowsRequest\Exception;
use Core\RowsRequest\Filter;
use Core\RowsRequest\Filters;
use Core\RowsRequest\Order;
use Core\RowsRequest\Orders;
use Core\RowsRequest\Pagination;
use Core\RowsRequest\Storage;
use \Phalcon\Config;
use Phalcon\Http\Request;

/**
 * Config example:
 * [
 *      // Request storage
 *      Core\RowsRequest::CONF_STORAGE => [
 *
 *          // Request storage adapter name
 *          Core\RowsRequest::CONF_STORAGE_ADAPTER => 'Core\RowsRequest\Storage\Session',
 *
 *          // Storage adapter config
 *          Core\RowsRequest::CONF_STORAGE_CONFIG => [
 *
 *              // Session namespace
 *              Core\RowsRequest\Storage\Session::CONF_NAMESPACE => 'SampleTableRequest'
 *          ]
 *      ],
 *
 *      // Request defaults
 *      Core\RowsRequest::CONF_DEFAULTS => [
 *          Core\RowsRequest::KEY_PAGE => 1,
 *          Core\RowsRequest::KEY_PER_PAGE => 10,
 *          Core\RowsRequest::KEY_ORDER => null,
 *          Core\RowsRequest::KEY_FILTERS => [],
 *          Core\RowsRequest::KEY_COLS => null,
 *      ]
 * ]
 */

/**
 * Class RowsRequest
 *
 * @package Core
 */
class RowsRequest implements \Serializable
{
	use Configurable;

	/**
	 * Input params keys names
	 */
	const KEY_START = 'start';
	const KEY_PER_PAGE = 'length';
	const KEY_ORDER = 'order';
	const KEY_FILTERS = 'f';
	const KEY_COLS = 'columns';

	/**
	 * Config params names
	 */
	const CONF_STORAGE = 'storage';
	const CONF_STORAGE_ADAPTER = 'adapter';
	const CONF_STORAGE_CONFIG = 'config';
	const CONF_DEFAULTS = 'defaults';

	/**
	 * Storage instance
	 * @var Storage
	 */
	protected $_storage = null;

	/**
	 * Pagination value instance
	 * @var Pagination
	 */
	protected $_pagination = null;

	/**
	 * Order value instance
	 * @var Orders
	 */
	protected $_orders = null;

	/**
	 * Filters instance
	 * @var Filters
	 */
	protected $_filters = null;

	/**
	 * Columns instance
	 * @var Columns
	 */
	protected $_columns = null;

	/**
	 * Additional request params
	 * @var array
	 */
	protected $_params = [];

	/**
	 * RowsRequest constructor.
	 *
	 * @param Config|null $config
	 */
	public function __construct(Config $config = null)
	{
		if ($config !== null) {
			$this->setConfig($config);
		}

		$this->reset();
		$this->load();
	}

	/**
	 * Set data from core controller request
	 *
	 * @param Request $request
	 * @return RowsRequest
	 */
	public function setFromControllerRequest(Request $request) :self
	{
		return $this->setFromArray($request->get());
	}

    /**
     * Set from DataTables request array
     *
     * @param array $request
     * @return RowsRequest
     */
	public function setFromDtRequest(array $request) :self
    {
        $this->columns()->clear();
        $this->filters()->clear();

        if (isset($request[static::KEY_COLS])) {
            $cols = ($request[static::KEY_COLS] ?? []);
            unset($request[static::KEY_COLS]);
        } else {
            $cols = [];
        }

        foreach ($cols as $col) {
            if (empty($col['data'])) {
                continue;
            }
            $column = new Column($col['data'], !empty($col['name']) ? $col['name'] : null);
            $this->columns()->append($column);
            if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                $this->filters()->add(
                    new Filter($column->name(), $col['search']['value'])
                );
            }
        }

        if (isset($request[static::KEY_START]) || isset($request[static::KEY_PER_PAGE])) {
            if (isset($request[static::KEY_START])) {
                $this->pagination()->start((int)$request[static::KEY_START]);
                unset($request[static::KEY_START]);
            }
            if (isset($request[static::KEY_PER_PAGE])) {
                $perPage = (int)$request[static::KEY_PER_PAGE];
                $this->pagination()->perPage($perPage);
                if ($perPage === -1) {
                    $this->pagination()->enable(false);
                }
                unset($request[static::KEY_PER_PAGE]);
            }
        }

        if (isset($request[static::KEY_ORDER])) {
            $this->orders()->clear();
            foreach ($request[static::KEY_ORDER] as $orderData) {
                $col = $cols[$orderData['column']] ?? null;
                if (!$col || empty($col['data'])) {
                    continue;
                }
                $this->orders()->append(
                    new Order($col['data'], $orderData['dir'] ?? null)
                );
            }
            unset($request[static::KEY_ORDER]);
        }

        $this->setParams($request);

        return $this;
    }

	/**
	 * Set request data from array
	 *
	 * @param array $request
	 * @return RowsRequest
	 */
	public function setFromArray(array $request) :self
	{
		if (isset($request[static::KEY_START]) || isset($request[static::KEY_PER_PAGE])) {
			if (isset($request[static::KEY_START])) {
				$this->pagination()->start((int)$request[static::KEY_START]);
				unset($request[static::KEY_START]);
			}
			if (isset($request[static::KEY_PER_PAGE])) {
				$this->pagination()->perPage((int)$request[static::KEY_PER_PAGE]);
				unset($request[static::KEY_PER_PAGE]);
			}
		}

		if (isset($request[static::KEY_ORDER])) {
			$this->orders()->clear();
			if (is_array($request[static::KEY_ORDER])) {
				$this->orders()->setFromArray($request[static::KEY_ORDER]);
			} elseif ((string)$request[static::KEY_ORDER] !== '') {
				$this->orders()->append(Order::fromString((string)$request[static::KEY_ORDER]));
			}
			unset($request[static::KEY_ORDER]);
		}

		if (isset($request[static::KEY_FILTERS])) {
			$this->filters()->clear();
			if (is_array($request[static::KEY_FILTERS])) {
				$this->filters()->setFromArray($request[static::KEY_FILTERS]);
			}
			unset($request[static::KEY_FILTERS]);
		}

		if (isset($request[static::KEY_COLS])) {
			$this->columns()->clear();
			if (is_array($request[static::KEY_COLS])) {
				$this->columns()->setFromArray($request[static::KEY_COLS]);
			}
		}

		$this->setParams($request);

		return $this;
	}

	/**
	 * Get storage instance
	 *
	 * @return Storage
	 * @throws Exception
	 */
	public function storage() :Storage
	{
		if ($this->_storage === null) {
			$config = $this->config(static::CONF_STORAGE);
			if (!($config instanceof Config)) {
				$config = new Config();
			}
			if (!($class = $config->get(static::CONF_STORAGE_ADAPTER))) {
				$class = __CLASS__ . '\Storage\BlackHole';
			}
			if (!class_exists($class)) {
				throw new Exception('Class ' . $class . ' does not exist');
			}
			$adapter = new $class();
			if (!($adapter instanceof Storage)) {
				throw new Exception(
				    'Instance of ' . $class . ' is not a valid ' . __CLASS__ . '\Storage instance'
                );
			}
			$config = $config->get(static::CONF_STORAGE_CONFIG);
			if ($config instanceof Config) {
				$adapter->setConfig($config);
			}
			$this->_storage = $adapter;
		}

		return $this->_storage;
	}

	/**
	 * Save to storage
	 *
	 * @return RowsRequest
	 */
	public function save() :self
	{
		$this->storage()->save($this);

		return $this;
	}

	/**
	 * Load from storage
	 *
	 * @return RowsRequest
	 */
	public function load() :self
	{
		$this->storage()->load($this);

		return $this;
	}

	/**
	 * Get pagination value instance
	 *
	 * @return Pagination
	 */
	public function pagination() :Pagination
	{
		if ($this->_pagination === null) {
			$this->_pagination = new Pagination();
		}

		return $this->_pagination;
	}

	/**
	 * Set limit instance
	 *
	 * @param Pagination $pagination
	 * @return RowsRequest
	 */
	public function setPagination(Pagination $pagination) :self
	{
		$this->_pagination = $pagination;

		return $this;
	}

	/**
	 * Get order values instance
	 *
	 * @return Orders
	 */
	public function orders() :Orders
	{
		if ($this->_orders === null) {
			$this->_orders = new Orders();
		}

		return $this->_orders;
	}

	/**
	 * Set order values
	 *
	 * @param Orders $orders
	 * @return RowsRequest
	 */
	public function setOrder(Orders $orders) :self
	{
		$this->_orders = $orders;

		return $this;
	}

	/**
	 * Get filters instance
	 *
	 * @return Filters
	 */
	public function filters() :Filters
	{
		if ($this->_filters === null) {
			$this->_filters = new Filters();
		}

		return $this->_filters;
	}

	/**
	 * Set filters instance
	 *
	 * @param Filters $filters
	 * @return RowsRequest
	 */
	public function setFilters(Filters $filters) :self
	{
		$this->_filters = $filters;

		return $this;
	}

	/**
	 * Get columns list instance
	 *
	 * @return Columns
	 */
	public function columns() :Columns
	{
		if ($this->_columns === null) {
			$this->_columns = new Columns();
		}

		return $this->_columns;
	}

	/**
	 * Set columns list instance
	 *
	 * @param Columns $columns
	 * @return RowsRequest
	 */
	public function setColumns(Columns $columns) :self
	{
		$this->_columns = $columns;

		return $this;
	}

	/**
	 * Get params array
	 *
	 * @return array
	 */
	public function params() :array
	{
		return $this->_params;
	}

	/**
	 * Set params from array
	 *
	 * @param array $params
	 * @return RowsRequest
	 */
	public function setParams(array $params) :self
	{
		foreach ($params as $key => $value) {
			$this->setParam($key, $value);
		}

		return $this;
	}

	/**
	 * Clear params array
	 *
	 * @return RowsRequest
	 */
	public function clearParams() :self
	{
		$this->_params = [];

		return $this;
	}

	/**
	 * Get param value
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function param(string $key, $default = null)
	{
		return ($this->_params[$key] ?? $default);
	}

	/**
	 * Set param value
	 *
	 * @param string $key
	 * @param $value
	 * @return RowsRequest
	 */
	public function setParam(string $key, $value) :self
	{
		$this->_params[$key] = $value;

		return $this;
	}

	/**
	 * Delete one param
	 *
	 * @param string $key
	 * @return RowsRequest
	 */
	public function clearParam(string $key) :self
	{
		if (array_key_exists($key, $this->_params)) {
			unset($this->_params[$key]);
		}

		return $this;
	}

	/**
	 * Reset request
	 *
	 * @return RowsRequest
	 */
	public function reset() :self
	{
		$this->pagination()->start(1);
		$this->pagination()->perPage(Pagination::PER_PAGE_DEFAULT);

		$this->orders()->clear();
		$this->filters()->clear();
		$this->columns()->clear();

		$this->clearParams();

		if ($defaults = $this->config(static::CONF_DEFAULTS)) {
			if ($defaults instanceof Config) {
				$defaults = $defaults->toArray();
			}
			if (is_array($defaults)) {
				$this->setFromArray($defaults);
			}
		}

		return $this;
	}

	/**
	 * Get request as array
	 *
	 * @return array
	 */
	public function toArray() :array
	{
		$res = [
			static::KEY_START => $this->pagination()->start(),
			static::KEY_PER_PAGE => $this->pagination()->perPage(),
			static::KEY_ORDER => $this->orders()->toArray(),
			static::KEY_FILTERS => $this->filters()->toArray(),
			static::KEY_COLS => $this->columns()->toArray(),
		];

		foreach ($this->params() as $key => $value) {
			$res[$key] = $value;
		}

		return $res;
	}

	/**
	 * Get request data hash
	 *
	 * @return string
	 */
	public function hash() :string
	{
		return sha1($this->serialize());
	}

	/**
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 * @since 5.1.0
	 */
	public function serialize()
	{
		return serialize($this->toArray());
	}

	/**
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized <p>
	 * The string representation of the object.
	 * </p>
	 * @return void
	 * @since 5.1.0
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);

		if (is_array($data)) {
			$this->setFromArray($data);
		}
	}
}