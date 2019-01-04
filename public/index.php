<?php
header("content-type:text/html;charset=utf-8");
// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
// 绑定当前访问到index模块
define('BIND_MODULE','index');
// 定义配置文件目录和应用目录同级
define('CONF_PATH', __DIR__.'/../config/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';