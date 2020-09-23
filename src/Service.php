<?php

namespace iszsw\porter;

use think\facade\Route;

class Service extends \think\Service
{

    public function boot()
    {
        $this->registerRoutes(function (){
            $route_prefix = config('porter.route_prefix', '');
            Route::group($route_prefix, function () {
                $table_namespace = '\iszsw\porter\controller\Table@';
                Route::rule('/'     , $table_namespace . 'index');
                Route::rule('index' , $table_namespace . 'index');
                Route::rule('edit'  , $table_namespace . 'edit');
                Route::rule('create', $table_namespace . 'edit');
                Route::rule('menu'  , $table_namespace . 'menu');
                Route::rule('delete', $table_namespace . 'delete');
                Route::rule('change', $table_namespace . 'change');

                Route::group('page', function () {
                    $page_namespace = '\iszsw\porter\controller\Page@';
                    Route::rule('/', $page_namespace . 'index');
                    Route::rule('index' , $page_namespace . 'index');
                    Route::rule('edit'  , $page_namespace . 'edit');
                    Route::rule('create', $page_namespace . 'edit');
                    Route::rule('delete', $page_namespace . 'delete');
                    Route::rule('change', $page_namespace . 'change');
                    Route::rule('relation', $page_namespace . 'relation');
                });

                Route::group('fields', function () {
                    $fields_namespace = '\iszsw\porter\controller\Fields@';
                    Route::rule('/', $fields_namespace . 'index');
                    Route::rule('index' , $fields_namespace . 'index');
                    Route::rule('edit'  , $fields_namespace . 'edit');
                    Route::rule('create', $fields_namespace . 'edit');
                    Route::rule('delete', $fields_namespace . 'delete');
                    Route::rule('change', $fields_namespace . 'change');
                });

            });
        });

    }


}