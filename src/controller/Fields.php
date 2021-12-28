<?php

namespace iszsw\curd\controller;

use iszsw\curd\exception\CurdException;
use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;

class Fields extends Common
{

    /**
     * @param $value
     * @param $table 当前表
     *
     * @return array
     */
    public function relation($table, $value)
    {
        $value = array_filter(explode('/', $value));
        $value = array_map(
            function ($v)
            {
                return explode('.', $v)[0];
            }, $value
        );

        $data = [];
        switch ($count = count($value))
        {
            case 0: // 中间表
                $data = $this->getRelationTables(false, $count);
                break;
            case 1: // 本表外键
                $data = $this->getRelationFields($table, false, $count);
                break;
            case 2: // 中间表与本表的关联键
                $data = $this->getRelationFields($value[0], false, $count);
                break;
            case 3: // 中间表与关联表的关联键
                $data = $this->getRelationFields($value[0], false, $count);
                break;
            case 4: // 关联表
                $data = $this->getRelationTables(false, $count);
                break;
            case 5: // 关联表外键
                $data = $this->getRelationFields($value[4], false, $count);
                break;
            case 6: // 关联表可视字段名
                $data = $this->getRelationFields($value[4], true, $count);
                break;
        }

        return Helper::success('获取成功', ['list' => $data]);
    }

    private function getRelationTables($leaf = false, $key = '')
    {
        $data = [];
        foreach (Manage::tableNames() as $t)
        {
            $data[] = [
                'label' => $t,
                'value' => $t.fields\Form::CASCADER_SEPARATOR."{$key}",
                'leaf'  => $leaf,
            ];
        }

        return $data;
    }

    private function getRelationFields($table, $leaf = false, $key = '')
    {
        $data = [];
        foreach (Manage::instance()->fields($table) as $v)
        {
            if ( ! $v['relation'])
            {
                $data[] = [
                    'label' => $v['field'],
                    'value' => $v['field'].fields\Form::CASCADER_SEPARATOR."{$key}",
                    'leaf'  => $leaf,
                ];
            }
        }

        return $data;
    }

    public function index(string $table)
    {
        return $this->createTable(new fields\Table($table));
    }

    public function update(string $table, string $name = '')
    {
        return $this->createForm(new fields\Form($table, $name));
    }

    public function delete($table, $name)
    {
        if ( ! $name)
        {
            return Helper::error('请选择需要删除的字段');
        }
        Manage::instance()->delete($table, $name);

        return Helper::success('删除成功');
    }

    public function change($table, $name, $field, $value)
    {
        try
        {
            if ( ! $table || ! $field)
            {
                throw new CurdException("参数错误");
            }

            Manage::instance()->save(['table' => $table, 'fields' => [$name => [$field => $value]]]);
        } catch (\Exception $e)
        {
            return Helper::error($e->getMessage() ?: '修改失败');;
        }

        return Helper::success('修改成功');
    }

}
