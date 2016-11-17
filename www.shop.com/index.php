<?php
//验证版本
version_compare(PHP_VERSION,'5.3','>') OR die("版本不能低于5.3");
//开启调试模式
define('APP_DEBUG',true);
//生成Admin模块目录
define('BIND_MODULE','Home');
//定义资源服务器
define('RES_URL','http://www.shop.com');
//定义应用目录
define('APP_PATH',__DIR__.'/Application/');
//引入框架
require '../ThinkPHP/ThinkPHP.php';