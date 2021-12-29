<?php

namespace iszsw\curd;

use iszsw\curd\lib\Model;
use plugin\api\logic\ApiLogic;
use surface\Factory;
use think\facade\Route;

/**
 * 服务
 *
 * Author: zsw iszsw@qq.com
 */
class Service extends \think\Service
{

    /**
     * 注册路由
     *
     * 注册CURD全局配置
     */
    public function register()
    {
        $this->registerRoutes(function (){
            $route_prefix = config('curd.route_prefix', '');
            $route = Route::group($route_prefix, function () {
                $table_namespace = '\iszsw\curd\controller\Table@';
                Route::rule('/'     , $table_namespace . 'index');
                Route::rule('update/:table'  , $table_namespace . 'update');
                Route::post('delete/:table', $table_namespace . 'delete');
                Route::post('change/:table', $table_namespace . 'change');

                Route::group('fields', function () {
                    $fields_namespace = '\iszsw\curd\controller\Fields@';
                    Route::rule('create/:table'  , $fields_namespace . 'update');
                    Route::rule('update/:table/:name'  , $fields_namespace . 'update');
                    Route::post('delete/:table/:name', $fields_namespace . 'delete');
                    Route::post('change/:table/:name', $fields_namespace . 'change');
                    Route::rule('relation/:table', $fields_namespace . 'relation');
                    Route::rule(':table', $fields_namespace . 'index');
                });

                Route::group('page', function () {
                    $page_namespace = '\iszsw\curd\controller\Page@';
                    Route::rule('create/:_table', $page_namespace . 'update');
                    Route::rule('update/:_table'  , $page_namespace . 'update');
                    Route::post('delete/:_table', $page_namespace . 'delete');
                    Route::post('change/:_table', $page_namespace . 'change');
                    Route::rule('relation/:_table', $page_namespace . 'relation');
                    Route::rule(':_table', $page_namespace . 'index');
                });

            });

            if ($middleware = config('curd.middleware', null)){
                $route->middleware($middleware);
            }

        });

    }

    public function boot()
    {
        Factory::configure(config('curd.surface'));
    }

}
