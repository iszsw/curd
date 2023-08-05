<?php

namespace curd;

use curd\generator\Column;
use curd\exceptions\CurdException;
use curd\generator\Curd;
use curd\generator\FormColumn;
use curd\generator\Refersh;
use curd\generator\Create;
use curd\generator\TableColumn;
use curd\generator\TableOperate;

class Build
{

    private string $title = '';

    /**
     * 命名空间
     * @var string
     */
    private string $namespace = '';

    /**
     * 生成的类名
     * @var string
     */
    private string $class = '';

    /**
     * 文件位置
     * @var string
     */
    private string $path = '';

    /**
     * 引入的类名
     * @var array<Column>
     */
    private array $columns = [];


    const OPERATE_REFRESH = 1;
    const OPERATE_CREATE = 2;
    const OPERATE_UPDATE = 4;
    const OPERATE_DELETE = 8;

    private int $operate = 0;

    /**
     * 内容缓存
     * @var string
     */
    private string $content = '';

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param  string  $path    curd文件存储绝对路径
     *
     * @return $this
     * @throws CurdException
     */
    public function setPath(string $path): static
    {
        $path = str_replace("\\", "/", $path);
        if (!file_exists($path) && !@mkdir($path, 0777, true)) {
            throw new CurdException("[{$path}]目录创建失败，请检查目录权限");
        }
        $this->path = $path . (str_ends_with($path, "/") ? "" : "/");
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass(string $class): static
    {
        [$this->namespace, $this->class] = Helper::parseClass($class);
        return $this;
    }

    /**
     * @param  array  $column
     *
     * @return Column
     */
    public function createColumn(array $column): Column
    {
        return (new Column($column));
    }

    /**
     * 设置列
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setColumns(array $columns): static
    {
        if (is_array(reset($columns))) {
            array_map([$this, 'setColumns'], $columns);
        } else {
            $columns = $this->createColumn($columns);
            if ($columns->name && ($columns->table_type || $columns->search_type || $columns->form_type)) {
                if (!$columns['comment']) {
                    $columns['comment'] = $columns['name'];
                }
                $this->columns[] = $columns;
            }
        }
        return $this;
    }

    /**
     * 设置操作项
     *
     * @param int $operate
     *
     * @return $this
     */
    public function setOperate(int $operate): static
    {
        $this->operate = $operate;
        return $this;
    }

    /**
     * 创建页面
     *
     * @param  bool  $refresh
     *
     * @return void
     * @throws CurdException
     */
    public function create(bool $refresh = false): void
    {
        if (!$this->content || $refresh) {
            $columns = $this->columns;
            $sort_order = array_column($columns, 'sort');
            array_multisort($sort_order, SORT_ASC, $columns);
            $curd = (new Curd());

            // 刷新
            if ($this->operate & self::OPERATE_CREATE) {
                $curd->searchColumns($this->createGenerator(Create::class));
            }

            // 新增
            if ($this->operate & self::OPERATE_REFRESH) {
                $curd->searchColumns($this->createGenerator(Refersh::class));
            }


            foreach ($columns as $column) {
                if ($column['table_type']) $curd->tableColumns($this->tableColumnGenerator($this->createGenerator($column['table_type'], $column), $column));
                if ($column['search_type']) $curd->searchColumns($this->formColumnGenerator($column['search_type'], $column));
                if ($column['form_type']) $curd->formColumns($this->formColumnGenerator($column['form_type'], $column));
            }

            // 修改 | 删除
            if ($this->operate & (self::OPERATE_UPDATE | self::OPERATE_DELETE) ) {
                $curd->tableColumns(
                    $this->tableColumnGenerator($this->createGenerator(TableOperate::class)->operate(self::OPERATE_UPDATE | self::OPERATE_DELETE))
                );
            }

            $curd->setTitle($this->title)
                ->setNamespace($this->namespace)
                ->setClass($this->class);
            $this->content = $curd->template();
        }
        $this->save();
    }

    /**
     * 保存内容
     *
     * @return void
     */
    private function save(): void
    {
        ob_start();
        echo $this->content;
        $content = ob_get_clean();
        file_put_contents($this->path . Helper::camel($this->class) . ".php", $content);
    }

    /**
     * 注册生成器
     *
     * @template T
     * @param string|class-string<T> $name
     * @param Column|null $column
     * @return T|Generator
     * @throws CurdException
     */
    private function createGenerator(string $name, ?Column $column = null)
    {
        if (!class_exists($name)) {
            throw new CurdException("生成器类[$name]不存在");
        }
        $class = (new $name($column));
        if (!($class instanceof Generator)) {
            throw new CurdException("生成器类[$name]必须实现[" . Generator::class . "]接口");
        }
        return $class;
    }

    /**
     * @param Generator $generator
     * @param Column|null $column
     * @return Generator
     * @throws CurdException
     */
    private function tableColumnGenerator(Generator $generator, ?Column $column = null): Generator
    {
        return $this->createGenerator(TableColumn::class, $column)->preview($generator);
    }

    /**
     * @param class-string $component
     * @param Column $column
     * @return Generator
     * @throws CurdException
     */
    private function formColumnGenerator(string $component, Column $column): Generator
    {
        return $this->createGenerator(FormColumn::class, $column)->component($component);
    }


}

