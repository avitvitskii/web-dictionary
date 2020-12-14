<?php

namespace Core;

use Core\RowsRequest\Pagination;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Paginator\Adapter\Model;
use Phalcon\Paginator\Adapter\NativeArray;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\AdapterInterface;

class PaginatorFactory
{
    /**
     * Create paginator instance
     *
     * @param $data
     * @param Pagination $pagination
     * @return AdapterInterface
     */
    public static function create($data, Pagination $pagination) :AdapterInterface
    {
        $config = [
            'limit' => $pagination->perPage(),
            'page' => $pagination->page()
        ];

        if (is_array($data)) {
            return new NativeArray(
                [
                    'data' => $data
                ] + $config
            );
        }

        if ($data instanceof ResultsetInterface) {
            return new Model(
                [
                    'data' => $data
                ] + $config
            );
        }

        if ($data instanceof Criteria) {
            return new QueryBuilder(
                [
                    'builder' => $data->createBuilder()
                ] + $config
            );
        }

        if ($data instanceof Builder) {
            return new QueryBuilder(
                [
                    'builder' => $data
                ] + $config
            );
        }

        throw new Exception('Cannot create paginator from ' . gettype($data));
    }
}