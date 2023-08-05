<?php

namespace curd\view;

use curd\Query;
use curd\Config;
use surface\Component;
use surface\Functions;
use surface\components\Input;
use surface\components\Checkbox;
use surface\components\FormColumn;
use surface\components\TableColumn;
use surface\helper\Curd;

class Table
{

    use Curd;

    public function __construct(private Config $config)
    {
    }

    protected function describe(): string|Component
    {
        return (new Component("el-alert"))->props([
            "closable" => false,
            "type" => 'warning',
        ])->children([
            (new Component('h2'))->slot('title')->children("CURD页面构建器助手"),
            (new Component('div'))->children([
                "选择下面数据库源或者自定义源生成页面代码到指定目录，",
                (new Component('a'))->props(['target'=>'_black','href'=>"https://doc.zsw.ink"])->children(" 查看文档"),
            ])
        ]);
    }

    protected function tableOptions(): array
    {
        return [
            'request'         => ['url' => ""],
            'paginationProps' => ['default-page-size' => 999]
        ];
    }

    private function arr2op(array $options): array
    {
        $content = [];
        foreach ($options as $value => $label){
            $content[] = [
                'label' => $label,
                'value' => $value,
            ];
        }
        return $content;
    }

    protected function build(): void
    {

        $this->getSurface()->addStyle(<<<STYLE
<style>

.el-table thead th {
    background-color: #EFF1F7 !important;
}

.el-table table thead th{
    font-weight: normal;
    color: #000;
}

.el-table .cell{
    font-size: 15px;
}

.s-table .s-table-render {
    margin-top: 10px;
}

.s-table .title {
    font-size: 24px;
    color: #000;
    margin-right: 10px
}

.s-table .describe {
    font-size: 15px;
    color: #888
}
</style>
STYLE
);

        // 注册Fields组件
        $vModelComponent = json_encode($this->arr2op(Query::VMODEL_COMPONENTS), JSON_UNESCAPED_UNICODE);
        $listComponent = json_encode($this->arr2op(Query::LIST_COMPONENTS), JSON_UNESCAPED_UNICODE);
        ob_start();
        include __DIR__."/FieldsComponent.php";
        $component = ob_get_clean();
        $this->getSurface()->register(Functions::create($component));


        $table = $this->buildTable()->children([$this->addBtn("自定义")->slot('header')]);
        $this->getSurface()->append($table);
        if ($form = $this->buildForm()) $this->getSurface()->append($form);
    }

    protected function tableColumns(): array
    {
        return [
            (new TableColumn())->props(['label' => "表名称", 'prop' => 'table']),
            (new TableColumn())->props(['label' => "表描述", 'prop' => 'comment']),
            (new TableColumn())->props(['label' => "数据行数", 'prop' => 'rows']),
            (new TableColumn())->props(['label' => "存储引擎", 'prop' => 'engine']),
            (new TableColumn())->props(['label' => ''])->children([$this->editBtn('选择')]),
        ];
    }


    protected function formOptions(): array
    {
        return [
            'reset' => null,
            'submitAfter' => Functions::create($this->getDialogApi()." = false", ["data", "res"]),
        ];
    }

    protected function formDialogProps(): array
    {
        return [
            'title'                => '构建页面',
            'destroy-on-close'     => true,
            'close-on-click-modal' => false,
            'fullscreen'           => true,
        ];
    }

    protected function formColumns(): array
    {
        return [
            (new Input(['label' => "代码生成目录", 'name' => 'save_path']))
                ->suffix("完整文件路径，格式：/www/laravel/app/controller/curd")
                ->value($this->config->save_path ?? '')
                ->col(['span' => 13]),

            (new Input(['label' => "类名称", 'name' => 'class']))
                ->suffix("完整的命名空间+类名 格式：app\controller\curd\Users")
                ->col(['span' => 13]),

            (new Checkbox(['label' => "操作", 'name' => 'operate']))
                ->options(Query::HANDLER_LABELS)
                ->col(['span' => 13]),

            (new FormColumn(['el' => 'fields', 'name' => 'fields']))
                ->item(['label-width' => 1])
                ->col(['span' => 24])
        ];
    }

}
