<?php

namespace curd\generator;


use curd\Generator;

/**
 * 表格文本组件不作处理
 *
 * @package curd\generator
 */
class Text extends Generator
{

    public function template(): string
    {
        return "";
    }

}
