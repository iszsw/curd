<?php

namespace curd\generator;

use curd\Build;
use curd\Generator;

/**
 *
 * Class Text
 *
 * @package curd\generator
 * Author: zsw iszsw@qq.com
 */
class TableOperate extends Generator
{

    private int $operate;

    public function operate(int $operate): static
    {
        $this->operate = $operate;
        return $this;
    }

    public function template(): string
    {
        $operate = [];
        if ($this->operate & Build::OPERATE_UPDATE) {
            $operate[] = '$this->editBtn()';
        }
        if ($this->operate & Build::OPERATE_DELETE) {
            $operate[] = '$this->deleteBtn()';
        }
        return count($operate) ? '['.(implode(',', $operate)).']' : '';
    }

}
