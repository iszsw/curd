<?php

namespace iszsw\curd;

use surface\Factory;
use think\facade\Route;

class Service extends \think\Service
{

    public function register()
    {
        $this->registerRoutes(function (){
            $route_prefix = config('curd.route_prefix', '');
            Route::group($route_prefix, function () {
                $table_namespace = '\iszsw\curd\controller\Table@';
                Route::rule('/'     , $table_namespace . 'index');
                Route::rule('index' , $table_namespace . 'index');
                Route::rule('update'  , $table_namespace . 'update');
                Route::rule('create', $table_namespace . 'update');
                Route::rule('menu'  , $table_namespace . 'menu');
                Route::post('delete', $table_namespace . 'delete');
                Route::post('change', $table_namespace . 'change');

                Route::group('page', function () {
                    $page_namespace = '\iszsw\curd\controller\Page@';
                    Route::rule('/', $page_namespace . 'index');
                    Route::rule('index' , $page_namespace . 'index');
                    Route::rule('update'  , $page_namespace . 'update');
                    Route::rule('create', $page_namespace . 'update');
                    Route::post('delete', $page_namespace . 'delete');
                    Route::post('change', $page_namespace . 'change');
                    Route::rule('search', $page_namespace . 'search');
                    Route::rule('relation', $page_namespace . 'relation');
                });

                Route::group('fields', function () {
                    $fields_namespace = '\iszsw\curd\controller\Fields@';
                    Route::rule('/', $fields_namespace . 'index');
                    Route::rule('index' , $fields_namespace . 'index');
                    Route::rule('update'  , $fields_namespace . 'update');
                    Route::rule('create', $fields_namespace . 'update');
                    Route::post('delete', $fields_namespace . 'delete');
                    Route::post('change', $fields_namespace . 'change');
                    Route::rule('relation', $fields_namespace . 'relation');
                });

            });
        });

        Factory::configure(config('curd.surface'));
    }


}
