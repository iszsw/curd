<?php
/**
 * Author: zsw zswemail@qq.com
 * Date: 2019/11/25 9:39
 */

namespace iszsw\porter\controller;

use iszsw\porter\lib\Model;
use iszsw\porter\lib\Resolve1;
use iszsw\porter\lib\ResolveField;
use iszsw\porter\lib\ResolveTable;
use surface\helper\tp\Curd;
use surface\helper\tp\FormInterface;
use surface\helper\tp\TableInterface;

/**
 * 自动生成页面
 *
 * @package iszsw\porter\controller
 * Author: zsw zswemail@qq.com
 */
class Page
{

    use Curd;

    public $table;

    public function __construct()
    {
        $this->table = input('table');
        if ( !$this->table )
        {
            throw new \Exception("数据表不存在");
        }
    }

    public function index()
    {
        $table = (new ResolveTable($this->table));
        return $this->createTable(
            new class($table) implements TableInterface
            {
                /**
                 * @var ResolveTable
                 */
                private $table;

                public function __construct($table)
                {
                    $this->table = $table;
                }

                public function rules(): array
                {
                    return $this->table->getSearch();
                }

                public function defaults(): array
                {
                    return $this->table->getDefault();
                }

                public function column(): array
                {
                    return $this->table->getColumn();
                }

                public function search($where = [], $order = '', $page = 1, $row_num = 15): array
                {
                    return $this->table->getData($where, $order, $page, $row_num);
                }
            }
        );
    }

    public function change()
    {
        $model = Model::instance($this->table);
        $post    = input();
        if ($model->save($post)) {
            return json(['code' => 0, 'msg'  => '编辑成功', 'data' => []]);
        }
        return json(['code' => 1, 'msg'  => $model->getError(), 'data' => []]);
    }

    public function edit()
    {
        $table = (new ResolveField($this->table));

        if ($pk = input($table->pk)) {
            try{
                $table->setData($pk);
            }catch (\Exception $e) {
                return json(['code' => 1, 'msg'  => '数据不存在', 'data' => []]);
            }
        }

        return $this->createForm(
            new class($table) implements FormInterface
            {
                /**
                 * @var ResolveField
                 */
                private $form;

                public function __construct($table)
                {
                    $this->form = $table;
                }

                public function defaults(): array
                {
                    return $this->form->getDefault();
                }

                public function column(): array
                {
                    return $this->form->getColumn();
                }

                public function save()
                {
                    $post    = input();
                    $model   = Model::instance($this->form->table);
                    if (true === $status = $model->save($post)) {
                        return json(['code' => 0, 'msg'  => ((isset($post[$model->getPk()]) && $post[$model->getPk()]) ? '编辑' : '创建') . '成功', 'data' => []]);
                    }
                    return json(['code' => 1, 'msg'  => $status, 'data' => []]);
                }
            }
        );

    }

    public function delete()
    {
        $post  = input();
        $model = Model::instance($this->table);
        $pk = $model->getPk();
        if (($post[$pk] ?? false) && $model->destroy($post[$pk])) {
            return json(['code' => 0, 'msg'  => '删除成功', 'data' => []]);
        }
        return json(['code' => 1, 'msg'  => '删除失败', 'data' => []]);
    }

}