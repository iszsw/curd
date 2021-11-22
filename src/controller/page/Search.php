<?php

namespace iszsw\curd\controller\page;

use iszsw\curd\lib\Model;
use iszsw\curd\lib\ResolveField;
use surface\helper\FormAbstract;

class Search extends FormAbstract
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
        $this->form->setData(request()->except(['_table']));
    }

    public function rules(): array
    {
        $rules = [];
        foreach($this->form->fields as $k => $f) {
            if ($f['search_type'] !== '_') {
                $rules[$k] = $f['search'];
            }
        }
        return $rules;
    }

    public function columns(): array
    {
        return $this->form->getSearchColumns();
    }



}
