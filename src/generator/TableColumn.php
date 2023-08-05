<?php

namespace curd\generator;

use curd\Generator;

/**
 * 表格列生成器
 *
 * @package curd\generator
 */
class TableColumn extends Generator
{

    protected Generator $preview;

    /**
     * @param Generator $preview
     *
     * @return $this
     */
    public function preview(Generator $preview): static
    {
        $this->preview = $preview;
        return $this;
    }

    public function template(): string
    {
        $this->setUse(\surface\components\TableColumn::class);
        $name = $this->config ? $this->config['name'] : '';
        $comment = $this->config ? $this->config['comment'] : '';
        $preview = '';
        if (isset($this->preview)) {
            $this->setUse($this->preview->getUse());
            $preview = $this->preview->template();
            $preview = $preview ? "->children($preview)" : "";
        }

        return str_replace(
            ['{COMMENT}', '{NAME}', '{PREVIEW}'],
            [$comment, $name, $preview],
            '(new TableColumn())->props(["label" => "{COMMENT}", "prop" => "{NAME}"]){PREVIEW}');

    }


}
