<?php
/**
 * 示例程序：配置文件
 */
// 应用目录
 
//define(name,value,case_insensitive)
//参数	描述
//name	必需。规定常量的名称。
//value	必需。规定常量的值。
//case_insensitive	
//可选。规定常量的名称是否对大小写敏感。
//若设置为 true，则对大小写不敏感。默认是 false（大小写敏感）。

// dirname(__FILE__ )         获取当前文件绝对路径
//  __FILE__                  取得当前文件的绝对地址
// dirname(__FILE__)          取得当前文件所在的绝对目录
// dirname(dirname(__FILE__)) 取得当前文件的上一层目录名
define('APP_ROOT', dirname(__FILE__ ).'/');
// 模板目录
define('APP_TEMPLATE_ROOT', APP_ROOT.'template/');

// 输出调试信息，生成环境请去掉这行
define('APP_DEBUG', true);

// MYSQL数据库配置，若不配置，则默认不创建数据库连接
define('CONF_MYSQL_SERVER', '127.0.0.1:3306');    // 服务器
define('CONF_MYSQL_USER',   'root');              // 用户名
define('CONF_MYSQL_PASSWD', '');                  // 密码
define('CONF_MYSQL_DBNAME', 'cdcol');              // 数据库名
define('CONF_MYSQL_CHARSET', 'utf8');             // 数据库编码


// 载入Slimphp并初始化
// include 和 require 语句用于在执行流中插入写在其他文件中的有用的代码。
require(APP_ROOT.'../core.php');
// 引入sql处理类
require(APP_ROOT.'../sql.php');
// 引入router处理类
require(APP_ROOT.'../router.php');
// 引入upload处理类
require(APP_ROOT.'../upload.php');
// 引入debug处理类
require(APP_ROOT.'../debug.php');
// 引入手机处理类
//require(APP_ROOT.'../mobile.php');
APP::init();

