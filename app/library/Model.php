<?php

namespace Core;
use Core\Db\Expr;
use Core\Db\Value;
use Core\RowsRequest\Columns;
use Core\RowsRequest\Filters;
use Core\RowsRequest\Filter;
use Core\RowsRequest\Orders;
use Phalcon\Di;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\AdapterInterface;

/**
 * Class ModelAbstract
 */
abstract class Model extends \Phalcon\Mvc\Model
{
    /**
     * Metadata cache
     * @var array
     */
    protected static $_meta = null;

	/**
	 * List of savable columns.
	 * Set NULL to allow save any of available columns.
	 * @var array
	 */
	protected $_savable = null;

    /**
     * List of timestamp properties
     * Array format:
     * [
     *  // Default time col (nullable, no current time set)
     *  'time_col1',
     *  // Time col with options
     *  'time_col2' => [
     *      // Set NULL or zero if value is empty
     *      'nullable' => false,
     *      // Set current timestamp if value is empty
     *      'setCurrentIfEmpty' => true
     *  ]
     * ]
     * @var array
     */
	protected $_timeCols = [];

    /**
     * Get model's metadata static cache
     *
     * @return \StdClass
     */
	final public static function meta() :\StdClass
    {
        if (self::$_meta === null) {
            self::$_meta = [];
        }

        $class = get_called_class();

        if (!isset(self::$_meta[$class])) {
            $model = new static();
            $meta = $model->getModelsMetaData();
            self::$_meta[$class] = (object)[
                'primary' => $meta->getIdentityField($model),
                'cols' => $meta->getAttributes($model),
            ];
        }

        return self::$_meta[$class];
    }

    /**
     * Get primary column name
     *
     * @return string
     */
    public static function primary() :string
    {
        return self::meta()->primary;
    }

    /**
     * Get table columns names
     *
     * @param bool $combine
     * @return array
     */
    public static function getCols(bool $combine = false) :array
    {
        $cols = self::meta()->cols;

        if ($combine) {
            $cols = array_combine($cols, $cols);
        }

        return $cols;
    }

    /**
     * Get columns list prepended with class name
     *
     * @param bool $combine
     * @return array
     */
    public static function getColsFull(bool $combine = false) :array
    {
        $cols = self::meta()->cols;
        $class = get_called_class();

        $cols = array_map(
            function ($col) use (&$class) {
                return $class . '.' . $col;
            },
            $cols
        );

        if ($combine) {
            $cols = array_combine($cols, $cols);
        }

        return $cols;
    }

	/**
	 * Get input data validators array in format
	 * [
	 *      '<col>' => [
	 *          'Validator1',
	 *          'Validator2' => [<validator options>],
	 *          ...
	 *      ]
	 * ]
	 *
	 * @return array
	 */
	public static function getInputValidators() :array
	{
		return [];
	}

	/**
	 * Get input filters array in format
	 * [
	 *      '<col1>' => [
	 *          'filter1',
	 *          'filter2',
	 *          ...
	 *      ],
	 *      '<col2>' => 'filter3',
	 *      ...
	 * ]
	 *
	 * @return array
	 */
	public static function getInputFilters() :array
	{
		return [];
	}

	/**
	 * Get list of filters for cols
	 * which have no their own filters
	 *
	 * @return array
	 */
	public static function getInputEscapeFilters() :array
	{
		return [
			'striptags',
			'trim'
		];
	}

    /**
     * Get list of fields names to skip them while filtering
     *
     * @return array
     */
	public static function getInputSkipFiltersFields() :array
    {
        return [];
    }

	/**
	 * Filter input data
	 *
	 * @param array $data
	 * @return array
	 */
	public static function filterInput(array $data) :array
	{
		$filters = static::getInputFilters();
		$escape = static::getInputEscapeFilters();

		foreach ($data as $key => $value) {
		    if (in_array($key, static::getInputSkipFiltersFields(), true)) {
		        continue;
            }
			$filter = new \Phalcon\Filter();
			$fNames = ($filters[$key] ?? $escape);
			$data[$key] = ($fNames ? $filter->sanitize($value, $fNames) : $value);
		}

		return static::afterFilterInput($data);
	}

    /**
     * Process data after filter
     *
     * @param array $data
     * @return array
     */
	public static function afterFilterInput(array $data) :array
    {
        return $data;
    }

    /**
     * Get paginator for list
     *
     * @param RowsRequest $request
     * @return \Phalcon\Paginator\AdapterInterface
     */
	public static function getPaginator(RowsRequest $request) :AdapterInterface
    {
        $select = static::_getSelectForPaginator($request);

        if (!$request->pagination()->enable()) {
            $request->pagination()->start(0);
            $request->pagination()->perPage(self::count());
        }

        return PaginatorFactory::create($select, $request->pagination());
    }

	/**
	 * Export all model rows
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function export() :string
	{
		$rows = static::_getExportRows();
		$rows = DataSet::prependWithNames($rows, true);

		/**
		 * @var DataStorage $ds
		 */
		$ds = Di::getDefault()->get('ds');
		$path = File::randomName(
			$ds->dir('export'),
			strtolower(get_called_class()) . '-',
			'xlsx'
		);
		File::registerTemp($path);

		$writer = new \XLSXWriter();
		$writer->writeSheet($rows, get_called_class());
		$writer->writeToFile($path);

		return $path;
	}

    /**
     * Get item ID
     *
     * @return int
     */
    public function id() :int
    {
        $primary = static::primary();

        return (int)$this->{$primary};
    }

	/**
	 * Get new Expr instance
	 *
	 * @param string $data
	 * @return Expr
	 */
	public function expr(string $data) :Expr
	{
		return new Expr($data);
	}

	/**
	 * Get new Value instance
	 *
	 * @param $value
	 * @return Value
	 */
	public function val($value = null) :Value
	{
		return new Value($value);
	}

	/**
	 * Get list of savable columns
	 *
	 * @return array
	 */
	public function savable() :array
	{
		if ($this->_savable === null) {
			$this->_savable = $this->getCols();
		}

		return $this->_savable;
	}

	/**
	 * Initialize method for model.
	 */
	public function initialize()
	{
		$this->setSource(str_replace('\\', '_', get_class($this)));
	}

	/**
	 * Validations and business logic
	 *
	 * @return boolean
	 */
	public function validation() :bool
	{
		$validation = Validation::fromArray(
			$this->getInputValidators(),
			['model' => $this]
		);

		return $this->validate($validation);
	}

    /**
     * Before save row
     */
    public function beforeSave()
    {
    }

    /**
     * After fetch
     */
    public function afterFetch()
    {
    }

    /**
     * After save
     */
    public function afterSave()
    {
        return $this->afterFetch();
    }

	/**
	 * Get rows for export
	 *
	 * @return array
	 */
	protected static function _getExportRows() :array
	{
		$rows = static::find();
		$rows = $rows->toArray();

		return $rows;
	}

    /**
     * Convert timestamp properties into integers
     *
     * @return Model
     */
	protected function _serializeTimeCols() :self
    {
        foreach ($this->_timeCols as $key => $val) {
            $item = $this->_timeColIteration($key, $val);
            $value = $this->{$item->col};

            if (empty($value)) {
                if ($item->options->setCurrentIfEmpty) {
                    $value = new Timestamp();
                    $value = $value->get();
                } elseif ($item->options->nullable) {
                    $value = null;
                } else {
                    $value = 0;
                }
            } else {
                if (!($value instanceof Timestamp)) {
                    $value = new Timestamp($value);
                }
                $value = $value->get();
            }

            $this->{$item->col} = $value;
        }

        return $this;
    }

    /**
     * Convert timestamp properties into Timestamp
     *
     * @return Model
     */
    protected function _unserializeTimeCols() :self
    {
        foreach ($this->_timeCols as $key => $val) {
            $item = $this->_timeColIteration($key, $val);
            $value = $this->{$item->col};

            if (empty($value)) {
                if ($item->options->setCurrentIfEmpty) {
                    $value = new Timestamp();
                } elseif ($item->options->nullable) {
                    $value = null;
                } else {
                    $value = 0;
                }
            } else {
                if (!($value instanceof Timestamp)) {
                    $value = new Timestamp($value);
                }
            }

            $this->{$item->col} = $value;
        }

        return $this;
    }

    /**
     * Prepare time col iteration item data
     *
     * @param $key
     * @param $val
     * @return \StdClass
     */
    protected function _timeColIteration($key, $val) :\StdClass
    {
        if (is_string($val)) {
            $col = $val;
            $options = [];
        } elseif (is_array($val)) {
            $col = $key;
            $options = $val;
        } else {
            throw new Exception('Unknown time col data format');
        }

        if (!property_exists($this, $col)) {
            throw new Exception(
                'Property ' . $col . ' does not exist in ' . get_class($this)
            );
        }

        $options += [
            'nullable' => true,
            'setCurrentIfEmpty' => false
        ];

        return (object)[
            'col' => $col,
            'options' => (object)$options
        ];
    }

    /**
     * Get select for paginator
     *
     * @param RowsRequest $request
     * @return Criteria
     */
    protected static function _getSelectForPaginator(RowsRequest $request) :Criteria
    {
        $select = static::_getBaseSelectForPaginator($request);

        static::_selectApplyColumns($select, $request->columns(), $request);
        static::_selectApplyFilters($select, $request->filters(), $request);
        static::_selectApplyOrders($select, $request->orders(), $request);
        static::_selectApplyParams($select, $request->params(), $request);

        return $select;
    }

    /**
     * Return select base for paginator
     *
     * @param RowsRequest $request
     * @return Criteria
     */
    protected static function _getBaseSelectForPaginator(RowsRequest $request) :Criteria
    {
        return $select = self::query();
    }

    /**
     * Apply request columns to select
     *
     * @param Criteria $select
     * @param Columns $columns
     * @param RowsRequest $request
     */
    protected static function _selectApplyColumns(Criteria $select, Columns $columns, RowsRequest $request)
    {
    }

    /**
     * Apply request filters to select
     *
     * @param Criteria $select
     * @param Filters $filters
     * @param RowsRequest $request
     */
    protected static function _selectApplyFilters(Criteria $select, Filters $filters, RowsRequest $request)
    {
        $filters->removeEmpty();

        foreach ($filters as $filter) {
            static::_selectApplyFilter($select, $filter, $request);
        }
    }

    /**
     * Apply request filter to select
     *
     * @param Criteria $select
     * @param Filter $filter
     * @param RowsRequest $request
     */
    protected static function _selectApplyFilter(Criteria $select, Filter $filter, RowsRequest $request)
    {
        $cols = static::getCols();

        if (in_array($filter->col(), $cols, true)) {
            $value = $filter->toDbValue();
            $select->andWhere(
                $value->eqExprPrepared(get_called_class() . '.' . $filter->col()),
                $value->bind()
            );
        }
    }

    /**
     * Apply request orders to select
     *
     * @param Criteria $select
     * @param Orders $orders
     * @param RowsRequest $request
     */
    protected static function _selectApplyOrders(Criteria $select, Orders $orders, RowsRequest $request)
    {
        $order = $orders->__toString();

        if (trim($order) !== '') {
            $select->orderBy($order);
        }
    }

    /**
     * Apply request params to select
     *
     * @param Criteria $select
     * @param array $params
     * @param RowsRequest $request
     */
    protected static function _selectApplyParams(Criteria $select, array $params, RowsRequest $request)
    {
    }
}