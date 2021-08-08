<?php
/**
 * Author: zsw zswemail@qq.com
 */

namespace iszsw\curd\controller;

use iszsw\curd\Helper;
use iszsw\curd\lib\Model;
use iszsw\curd\lib\ResolveField;

/**
 * 自动生成页面
 *
 * @package iszsw\curd\controller
 * Author: zsw zswemail@qq.com
 */
class Page extends Common
{

    private $table;

    public function __construct()
    {
        $this->table = request()->param('_table');
    }

    public function index()
    {
        return $this->createTable(new page\Table($this->table));
    }

    public function update()
    {
        return $this->createForm(new page\Form($this->table));
    }

    public function change($field, $value)
    {
        $model = (new ResolveField($this->table));
        $pkKey = $model->pk;
        $pk = input($pkKey);

        if (!$pk) {
            return Helper::error("修改失败");
        }

        if ($model->save([$pkKey => $pk, $field => $value]))
        {
            return Helper::success('编辑成功');
        }

        return Helper::error($model->getError());
    }

    public function delete()
    {
        $model = (new ResolveField($this->table));
        $pkKey = $model->pk;
        $data = (array)input($pkKey);

        if ($model->delete($data))
        {
            return Helper::success('删除成功');
        }

        return Helper::error($model->getError());

    }

}
