<?php

namespace iszsw\curd\controller;

use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;

class Table extends Common
{

    public function index()
    {
        return $this->createTable(new table\Table());
    }

    public function update(string $table)
    {
        return $this->createForm(new table\Form($table));
    }

    /**
     * 删除配置
     * @param string $table
     *
     * @return array
     * @throws \Exception
     */
    public function delete(string $table)
    {
        Manage::instance()->delete($table);
        return Helper::success("删除成功");
    }

    /**
     * 修改状态(暂只允许修改status字段)
     *
     * @param string $table
     * @param string $field
     * @param bool   $value
     *
     * @return array
     */
    public function change(string $table, string $field, bool $value)
    {
        try
        {
            Manage::instance()->save(
                [
                    'table'=>$table,
                    'status'=>$value,
                ]
            );
        } catch (\Exception $e)
        {
            return Helper::error($e->getError());
        }
        return Helper::success('修改成功');
    }

}
