<?php

namespace curd\generator;

use curd\Generator;

/**
 *
 * Class Text
 *
 * @package curd\generator
 * Author: zsw iszsw@qq.com
 */
class Refersh extends Generator
{

    public function template(): string
    {
        return '$this->reloadBtn()';
    }

}
