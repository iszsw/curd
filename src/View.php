<?php

namespace curd;


use curd\exceptions\CurdException;
use curd\view\Table;

class View
{

    protected Request $request;

    private string $error;

    public function __construct(protected Config $config)
    {
        $this->request = new Request();
    }

    /**
     * 构建页面
     *
     * @return array|string
     * @throws exceptions\CurdException
     */
    public function fetch(): array|string
    {
        if ($this->request->isPost()) {
            return $this->handle($this->request->post())
                ? $this->request->success(msg: "页面代码生成成功")
                : $this->request->error($this->error);
        } if ($this->request->isAjax()) {
            return $this->request->success(['data' => (new Query($this->config))->tablesInfo()]);
        }
        return (new Table($this->config))->view();
    }

    private function handle(array $input = []): bool
    {
        try{
            $save_path = $input['save_path'] ?: $this->config->save_path;
            if (!$save_path) {
                throw new CurdException("请填写生成代码的目录");
            }
            $class = $input['class'] ?? '';
            if (!$class) {
                throw new CurdException("请填写完整的类名称，命名空间+类名");
            }
            $fields = $input['fields'] ?? [];
            if (empty($fields)) {
                throw new CurdException("没有列项，请添加列");
            }
            $operate = $input['operate'] ?? [];

            (new Build())
                ->setTitle($input['comment'] ?? '')
                ->setPath($save_path)
                ->setClass($class)
                ->setColumns($fields)
                ->setOperate(array_sum($operate))
                ->create();

        }catch (CurdException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }


}
