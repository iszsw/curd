<?php

namespace iszsw\curd\controller\page;

use iszsw\curd\lib\ResolveTable;
use surface\Component;
use surface\helper\FormAbstract;
use surface\helper\TableAbstract;

class Table extends TableAbstract
{

    /**
     * @var ResolveTable
     */
    private $table;

    /**
     * @var string
     */
    private $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->table = (new ResolveTable($this->tableName));
    }

    public function search(): ?FormAbstract
    {
        return new Search($this->tableName);
    }

    public function header(): ?Component
    {
        return $this->table->getHeader();
    }

    public function options(): array
    {
        return $this->table->getOptions();
    }

    public function columns(): array
    {
        return $this->table->getColumn();
    }

    public function pagination(): ?Component
    {
        return $this->table->getPagination();
    }

    public function data($where = [], $order = '', $page = 1, $limit = 15): array
    {
        $where = array_filter($where, function ($w) {
            return !in_array($w[0], ['_table']);
        });
        return $this->table->getData($where, $order, $page, $limit);
    }


}
