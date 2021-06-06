<?php

return [
    'route_prefix' => 'curd',     // 路由前缀 通过访问 /curd.html 访问表格页面
    'save' => \iszsw\curd\lib\Manage::FILE_NAME, // 默认 存储类型
    'engine' => [
        \iszsw\curd\lib\Manage::FILE_NAME => [
            'save_path' => runtime_path('curd'), // 数据保存地址 确保路径的可读写
        ]
    ],

    /*
     * surface配置 配置优先级: 自定义配置 > 默认配置
     *
     * globals参数为VUE组件（props，domProps，style，class ....）
     *
     * 前端使用 ElementUI
     *
     * Form.globals.props 参数 https://element.eleme.cn/#/zh-CN/component/form#form-attributes
     * Table.globals.props 参数 https://element.eleme.cn/#/zh-CN/component/table#table-attributes
     */
    'surface' => [
        'table' => [
            'globals' => [
                'props' => [ // props参数
                    'emptyText' => "没有更多",
                ],
            ],
        ],
        'form'  => [
            'globals' => [

            ],
            'upload' => [
                'props' => [
                    'action' => '/upload.php',
                ]
            ]
        ],
    ]
];
