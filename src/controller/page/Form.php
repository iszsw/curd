<?php

namespace iszsw\curd\controller\page;

use iszsw\curd\lib\Model;
use iszsw\curd\lib\ResolveField;
use surface\helper\AbstractForm;

class Form extends AbstractForm
{
    /**
     * @var ResolveField
     */
    private $form;

    /**
     * @var string
     */
    private $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
        $this->form = (new ResolveField($this->tableName));
        if ($pk = input($this->form->pk, null)) {
            $this->form->setData($pk);
        }
    }

    public function options(): array
    {
        return $this->form->getOptions();
    }

    public function columns(): array
    {
        return $this->form->getColumns();
    }

    public function save(): bool
    {
        if (true !== $error = Model::instance($this->form->table)->save(input())) {
            $this->error = $error;
            return false;
        }
        return true;
    }


}
