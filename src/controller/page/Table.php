<?php

namespace iszsw\curd\controller\page;

use iszsw\curd\lib\ResolveTable;
use surface\helper\TableInterface;
use surface\table\components\Header;
use surface\table\components\Pagination;

class Table implements TableInterface
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

    public function header(): ?Header
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

    public function pagination(): ?Pagination
    {
        return $this->table->getPagination();
    }

    public function data($where = [], $order = '', $page = 1, $limit = 15): array
    {
        return $this->table->getData($where, $order, $page, $limit);
    }


}
