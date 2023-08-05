<?php

namespace curd\generator;

use curd\Generator;
use surface\Component;

class Curd extends Generator
{

    private string $title = "";

    private string $class = "";

    private string $namespace = "";

    /**
     * @var array<Generator>
     */
    private array $tableColumns = [];

    /**
     * @var array<Generator>
     */
    private array $searchColumns = [];

    /**
     * @var array<Generator>
     */
    private array $formColumns = [];

    /**
     * @param  array<Generator>|Generator  $columns
     *
     * @return $this
     */
    public function tableColumns(array|Generator $columns): static
    {
        if (is_array($columns)) {
            array_map([$this, 'tableColumns'], $columns);
        } else {
            $this->tableColumns[] = $columns;
        }

        return $this;
    }

    /**
     * @param  array<Generator>|Generator  $columns
     *
     * @return $this
     */
    public function searchColumns(array|Generator $columns): static
    {
        if (is_array($columns)) {
            array_map([$this, 'searchColumns'], $columns);
        } else {
            $this->searchColumns[] = $columns;
        }

        return $this;
    }

    /**
     * @param  array<Generator>|Generator  $columns
     *
     * @return $this
     */
    public function formColumns(array|Generator $columns): static
    {
        if (is_array($columns)) {
            array_map([$this, 'formColumns'], $columns);
        } else {
            $this->formColumns[] = $columns;
        }

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function setNamespace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    private function resolveTableColumns(): string
    {
        $columns = [];
        foreach ($this->tableColumns as $column) {
            $columns[] = $column->template();
            $this->setUse($column->getUse());
        }
        $content = implode(",\n\t\t\t", $columns);
        if ($content) {
            $content = "\n\t\t\t$content\n\t\t";
        }
        return $content;
    }

    private function resolveSearchColumns(): string
    {
        $columns = [];
        foreach ($this->searchColumns as $column) {
            $columns[] = $column->template();
            $this->setUse($column->getUse());
        }
        $content = implode(",\n\t\t\t", $columns);
        if ($content) {
            $content = "\n\t\t\t$content\n\t\t";
        }
        return $content;
    }

    private function resolveFormColumns(): string
    {
        $columns = [];
        foreach ($this->formColumns as $column) {
            $columns[] = $column->template();
            $this->setUse($column->getUse());
        }
        $content = implode(",\n\t\t\t", $columns);
        if ($content) {
            $content = "\n\t\t\t$content\n\t\t";
        }
        return $content;
    }

    private function getUseStr(): string
    {
        return array_reduce(
            $this->getUse(),
            function ($str, $use)
            {
                return $str."use {$use};\n";
            },
            ''
        );
    }

    public function template(): string
    {
        $this->setUse(Component::class);
        $table = $this->resolveTableColumns();
        $search = $this->resolveSearchColumns();
        $form = $this->resolveFormColumns();
        return str_replace(
            ["{NAMESPACE}", "{USE}", "{CLASS}", "{TITLE}", "{SEARCH-COLUMNS}", "{TABLE-COLUMNS}", "{FORM-COLUMNS}"],
            [$this->namespace, $this->getUseStr(), $this->class, $this->title, $search, $table, $form],
            file_get_contents(__DIR__."/stubs/curd.stub")
        );
    }


}
