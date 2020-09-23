<?php

namespace iszsw\porter\controller;

use iszsw\porter\lib\Manage;
use surface\form\Form;
use surface\helper\tp\Curd;
use iszsw\porter\model\Table as TableModel;
use surface\helper\tp\FormInterface;
use surface\helper\tp\TableInterface;
use surface\table\Table as STable;

class Table
{

    use Curd;

    protected function getTableModel()
    {
        return new class implements TableInterface {

            public function rules(): array
            {
                return [];
            }

            public function defaults(): array
            {
                $btn = create_table_btn(6, ['table']);
                $dataUrl = builder_table_url('fields/index');
                $btn['operations'][] = STable::button('page', '', [
                    'title' => '字段信息',
                    'url' => $dataUrl,
                    'method' => 'get',
                    'refresh' => true,
                    'params' => ['table'],
                ], 'fa fa-list');

                $menuUrl = builder_table_url('menu');
                $btn['operations'][] = STable::button('confirm', '', [
                    'text' => '确认生成地址？',
                    'url' => $menuUrl,
                    'method' => 'post',
                    'params' => ['table'],
                ], 'fa fa-file-text');

                return array_merge($btn, [
                    'pk' => "table",
                    'title' => "表管理",
                    'description' => '表管理',
                    'pageShow' => false,
                    'searchShow' => true,
                ]);
            }

            public function column(): array
            {
                return [
                    "table" => TableModel::$labels['table'],
                    "title" => TableModel::$labels['title'],
                    "description" => TableModel::$labels['description'],
                    "page" => [
                        'type' => 'in',
                        'title' => TableModel::$labels['page'],
                        'options' => TableModel::$statusLabels
                    ],
                    "rows" => TableModel::$labels['rows'],
                    "engine" => TableModel::$labels['engine'],
                    "extend" => [
                        'type' => 'longText',
                        'title' => TableModel::$labels['extend']
                    ],
                ];
            }

            public function search($where = [], $order = '', $page = 1, $row_num = 15): array
            {
                return [
                    'list' => Manage::instance()->tables()
                ];
            }
        };
    }


    public function index()
    {
        return $this->createTable($this->getTableModel());
    }


    protected function getFormModel()
    {
        return new class implements FormInterface {

            public function defaults(): array
            {
                return [];
            }

            public function column(): array
            {
                $table = input('table', '');
                if (!$table) {
                    return json(['code' => 1, 'msg'  => "数据表不存在", 'data' => []]);
                }
                $model = Manage::instance()->tables($table);
                if (!$model || count($model) !== 1) {
                    return json(['code' => 1, 'msg'  => "参数错误", 'data' => []]);
                }
                $model = $model[0];

                return [
                    Form::text('table', TableModel::$labels['table'], $model['table'])->props(['readonly' => true]),
                    Form::text('title', TableModel::$labels['title'], $model['title']),
                    Form::textarea('description', TableModel::$labels['description'], $model['description']),
                    Form::switcher('page', TableModel::$labels['page'], $model['page'])->addOptions(TableModel::$statusLabels),
                    Form::switcher('auto_timestamp', TableModel::$labels['auto_timestamp'], $model['auto_timestamp'])->addOptions(TableModel::$statusLabels)->props(['mark' => '是否需要自动写入时间戳 自动更新create_time,update_time']),
                    Form::json('btn', TableModel::$labels['btn'], $model['btn'])
                        ->props(['mark' => '默认操作edit|create|delete <br>自定义键名 : url 提交地址<br>键值之间用,分隔<table>
		<tr><th>参数</td><td>[默认]可选值</th></tr>
		<tr><td>位置local</td><td>top|right</td></tr>
		<tr><td>类型type</td><td>page|submit|confirm|html|alert</td></tr>
		<tr><td>标题title</td><td>子页面的Title</td></tr>
		<tr><td>参数params</td><td>携带列字段提交 多个参数用|分隔</td></tr>
		<tr><td>样式faClass</td><td>[fa fa-list]</td></tr>
		<tr><td>提交后刷新refresh</td><td>[true] true|false</td></tr>
		<tr><td>提示文本text</td><td>alert下提示的文字</td></tr>
		<tr><td>提交类型method</td><td>[POST] POST|GET</td></tr
	</table>']),
                    Form::json('extend', TableModel::$labels['extend'], $model['extend'])->props(['mark' => '表格页面配置 page_row_num => 10']),
                ];
            }

            public function save()
            {
                $post = input();
                try {
                    Manage::instance()->save($post);
                } catch (\Exception $e) {
                    return json(['code' => 1, 'msg'  => '保存失败', 'data' => []]);
                }
                return json(['code' => 0, 'msg'  => '保存成功', 'data' => []]);

            }
        };
    }

    public function edit()
    {
        return $this->createForm($this->getFormModel());
    }

    /**
     * 生成菜单
     * Author: zsw zswemail@qq.com
     */
    public function menu()
    {
        $url = builder_table_url('page/index', ['table' => input('table')], true);
        return json(['code' => 0, 'msg'  => "页面地址" . PHP_EOL . $url, 'data' => []]);
    }

    /**
     * 删除配置
     * Author: zsw zswemail@qq.com
     */
    public function delete()
    {
        $table = input('table');
        Manage::instance()->delete($table);
        return json(['code' => 0, 'msg'  => "删除成功", 'data' => []]);
    }

}
