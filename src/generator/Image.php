<?php

namespace curd\generator;

use curd\Generator;
use surface\Component;

/**
 *
 * Class Text
 *
 * @package curd\generator
 * Author: zsw iszsw@qq.com
 */
class Image extends Generator
{

    public function template(): string
    {
        $this->setUse(Component::class);
        return '(new Component(["el" => "el-image"]))->props([":src" => "", "style" => ["width" => "50px"]])';
    }

}
