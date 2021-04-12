<?php
/**
 * Author: zsw zswemail@qq.com
 */

namespace iszsw\curd\controller;

use iszsw\curd\Helper;
use iszsw\curd\lib\Model;
use think\exception\HttpException;

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
        $this->table = $_GET['_table'] ?? '';
        if ( !$this->table )
        {
            throw new HttpException(404);
        }
    }

    public function index()
    {
        return $this->createTable(new page\Table($this->table));
    }

    public function search()
    {
        return $this->createForm(new page\Search($this->table));
    }

    public function update()
    {
        return $this->createForm(new page\Form($this->table));
    }

    public function change()
    {
        $model = Model::instance($this->table);
        $post  = input();
        if ($model->save($post)) {
            return Helper::success('编辑成功');
        }
        return Helper::error($model->getError());
    }

    public function delete()
    {
        $post  = input();
        $model = Model::instance($this->table);
        $pk = $model->getPk();
        if (($post[$pk] ?? false) && $model->destroy($post[$pk])) {
            return Helper::success('删除成功');
        }
        return Helper::error('删除失败');
    }

}
