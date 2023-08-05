<?php

namespace curd\generator;

use curd\Generator;
use curd\Helper;

/**
 * 表格列生成器
 *
 * @package curd\generator
 */
class FormColumn extends Generator
{

    protected string $component;

    /**
     * @param class-string $component
     *
     * @return $this
     */
    public function component(string $component): static
    {
        $this->component = $component;
        return $this;
    }


    public function template(): string
    {
        $content = '';
        if (isset($this->component) ) {
            [$namespace, $class] = Helper::parseClass($this->component);
            $this->setUse($this->component);

            $name = $this->config['name'];
            $comment = $this->config['comment'];
            $content = str_replace(
                ['{CLASS}','{COMMENT}', '{NAME}'],
                [$class, $comment, $name],
                '(new {CLASS}(["label"=>"{COMMENT}", "name"=>"{NAME}"]))');
        }

        return $content;
    }


}
