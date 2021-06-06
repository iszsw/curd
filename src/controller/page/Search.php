<?php

namespace iszsw\curd\controller\page;

use iszsw\curd\lib\Model;
use iszsw\curd\lib\ResolveField;
use surface\helper\AbstractForm;

class Search extends AbstractForm
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

    public function init(\surface\form\Form $form)
    {
        $form->search(true);
    }

    public function columns(): array
    {
        return $this->form->getSearchColumns();
    }



}
