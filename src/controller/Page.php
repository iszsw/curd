<?php
/**
 * Author: zsw iszsw@qq.com
 */

namespace iszsw\curd\controller;

use iszsw\curd\Helper;
use iszsw\curd\lib\Model;
use iszsw\curd\lib\ResolveField;

/**
 * 自动生成页面
 *
 * @package iszsw\curd\controller
 * Author: zsw iszsw@qq.com
 */
class Page extends Common
{

    public function index(string $_table)
    {
        return $this->createTable(new page\Table($_table));
    }

    public function update(string $_table)
    {
        return $this->createForm(new page\Form($_table));
    }

    public function change(string $_table, $field, $value)
    {
        $model = (new ResolveField($_table));
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

    public function delete(string $_table)
    {
        $model = (new ResolveField($_table));
        $pkKey = $model->pk;
        $data = (array)input($pkKey);

        if ($model->delete($data))
        {
            return Helper::success('删除成功');
        }

        return Helper::error($model->getError());

    }

}
