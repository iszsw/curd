<?php

namespace iszsw\curd\controller;

use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;

class Fields extends Common
{

    /**
     * @param $value
     * @param $table 当前表
     *
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     */
    public function relation($table, $value)
    {
        $value = array_filter(explode('/', $value));

        $data = [];
        switch (count($value))
        {
            case 0: // 中间表
                foreach (Manage::tableNames() as $t)
                {
                    $data[] = [
                        'label'    => $t['table'],
                        'value'    => $t['table'],
                        'leaf'     => false,
                    ];
                }
                break;
            case 1: // 本表外键
                $data = $this->getRelationFields($table);
                break;
            case 2: // 中间表与本表的关联键
                $data = $this->getRelationFields($value[0]);
                break;
            case 3: // 中间表与关联表的关联键
                $data = $this->getRelationFields($value[0]);
                break;
            case 4: // 关联表
                foreach (Manage::tableNames() as $t)
                {
                    $data[] = [
                        'label'    => $t['table'],
                        'value'    => $t['table'],
                        'leaf'     => false,
                    ];
                }
                break;
            case 5: // 关联表外键
                $data = $this->getRelationFields($value[4]);
                break;
            case 6: // 关联表可视字段名
                $data = $this->getRelationFields($value[4], true);
                break;
        }

        return Helper::success('', ['list' => $data]);
    }

    private function getRelationFields($table, $leaf = false)
    {
        $data = [];
        foreach (Manage::instance()->fields($table) as $v)
        {
            if ( ! $v['relation'])
            {
                $data[] = [
                    'label'    => $v['field'],
                    'value'    => $v['field'],
                    'leaf'     => $leaf,
                ];
            }
        }

        return $data;
    }

    public function index()
    {
        return $this->createTable(new fields\Table());
    }

    public function update()
    {
        return $this->createForm(new fields\Form());
    }

    public function delete($table, $field)
    {
        if ( ! $field) return Helper::error('请选择需要删除的字段');
        Manage::instance()->delete($table, $field);
        return Helper::success('删除成功');
    }

    public function change($table, $id, $field, $value)
    {
        try
        {
            if ( !$table || !$field || !$id) throw new \Exception("参数错误");
            Manage::instance()->save(['table' => $table, 'fields' => [$id => [$field=>$value]]]);
        } catch (\Exception $e)
        {
            return Helper::error($e->getMessage() ?: '修改失败');;
        }
        return Helper::success('修改成功');
    }

}
