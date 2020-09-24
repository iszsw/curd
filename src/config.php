<?php

return [
    'route_prefix' => 'table',     // 路由前缀 通过访问 /table 访问表格页面
    'save' => 'file', // 默认 存储类型
    'engine' => [
        'file' => [
            'save_path' => runtime_path('table'), // 数据保存地址 确保路径的可读写
        ]
    ]
];
