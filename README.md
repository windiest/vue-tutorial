SlimPHP 微型的PHP框架
==============

此框架仅有几个文件，其中包含了MySQL数据库、上传文件、调试信息、导入依赖文件、
模板和REST路由等一系列常用操作。适合用来快速编写大部分后台程序，所有文件均有详细注释，适合新手学习的框架。


初始化
=============

首先新建一个`config.php`文件，所有程序通过加载该文件来进行配置及初始化：

```php
<?php
/**
 * 配置文件
 */

// 当前应用的根目录
define('APP_ROOT', dirname(__FILE__ ).'/');
// 模板根目录
define('APP_TEMPLATE_ROOT', APP_ROOT.'template/');


// 输出调试信息，生成环境请去掉这行或设置为false
define('APP_DEBUG', true);


// MYSQL数据库配置，如果不定义数据库配置，则不自动连接数据库
define('CONF_MYSQL_SERVER', 'localhost:3306');  // 服务器，默认为 localhost:3306
define('CONF_MYSQL_USER',   'root');            // 用户名，默认为 root
define('CONF_MYSQL_PASSWD', '123456');          // 密码，默认为空
define('CONF_MYSQL_DBNAME', 'test');            // 数据库名，默认为空
define('CONF_MYSQL_PERMANENT', false);          // 使用使用永久连接，默认false


// 载入slimphp并初始化
require(APP_ROOT.'slimphp.php');
APP::init();
?>
```

在所有php程序中，均可载入`config.php`文件唉实现初始化slimphp：

```php
<?php
require('config.php');
// ...
?>
```


REST路由
===========

slimphp可以根据不同的请求方法来调用相应的处理函数完成请求，比如：

```php
<?php
require('config.php');

// 这里是公共部分的代码，所有请求方法都会执行下面的代码
echo '所有请求方法都会执行这里的代码';

// 定义处理GET请求的代码
function method_get () {
  echo 'GET请求方法的处理代码';
}

// 定义处理POST请求的代码
function method_post () {
  echo 'POST请求方法的处理代码';
}

// 定义处理DELETE请求的代码
function method_delete () {
  echo 'DELETE请求方法的处理代码';
}

// 定义处理PUT请求的代码
function method_put () {
  echo 'PUT请求方法的处理代码';
}

?>
```


操作MySQL数据库
===============

slimphp中提供了一个静态类 __SQL__ 来操作MySQL数据库：

* `SQL::connect($server = 'localhost:3306', $username = 'root', $password = '',$database = '');`
连接到数据库，当配置了数据库连接时，slimapp会自动执行此方法来连接到数据库，若你的
程序中已经通过`mysql_connect`来创建了一个数据库连接，可以不用再执行此方法连接数
据库；

* `SQL::getAll($sql)` 或 `SQL::getData($sql)` 查询SQL，并返回数组格式的结果，
失败返回`FALSE`；

示例代码数据库如下：

<table>
<tr><td>titel</td><td>interpret</td><td>jahr</td><td>id</td></tr>
<tr><td>Beauty</td><td>Ryuichi Sakamoto</td><td>1990</td><td>1</td></tr>
<tr><td>Glee</td><td>Groove Armada</td><td>2001</td><td>4</td></tr>
<tr><td>Glee</td><td>Bran Van 3000</td><td>1997</td><td>5</td></tr>
</table>

```php
//数据库为cdcol,表为cds                       输出如下
$sql = 'SELECT * FROM  `cds` LIMIT 0 , 30';  //title: titel     value: Beauty
$query=SQL::getAll($sql);                    //title: interpret value: Ryuichi Sakamoto
$query_first_array = $query[0];             //title: jahr      value: 1990
echo '<br>';                                 //title: id        value: 1
foreach($query_first_array as $k => $v){
  echo 'title: '.$k.' <br>value: '.$v.'<br><br>';
}
```

* `SQL::getOne($sql)` 或 `SQL::getLine($sql)` 查询SQL，仅返回第一条结果，
失败返回`FALSE`；

```php
$sql = 'SELECT * FROM  `cds`';  //array(4) {  ["titel"]     => string(6) "Beauty" 
$query=SQL::getOne($sql);       //            ["interpret"] => string(16)"Ryuichi Sakamoto" 
var_dump($query);               //            ["jahr"]      => string(4) "1990" 
                                //            ["id"]        => string(1) "1"                 }
```

* `SQL::update($sql)` 或 `SQL::runSql($sql)` 查询SQL，返回受影响的记录数，一般
用于执行插入或更新操作；

<table>
<tr><td>titel</td><td>interpret</td><td>jahr</td><td>id</td></tr>
<tr><td>Beauty</td><td>Ryuichi Sakamoto</td><td>1990</td><td>1</td></tr>
<tr><td>Glee</td><td>Groove Armada</td><td>2001</td><td>4</td></tr>
<tr><td>Glee</td><td>Bran Van 3000</td><td>1997</td><td>5</td></tr>
<tr><td>Hello</td><td>My friends</td><td>2015</td><td>2</td></tr>
</table>

```php
$sql = 'insert into cds(titel, interpret, jahr ,id) values("Hello", "My friends", 2015, 2)';
$query = SQL::update($sql);
var_dump($query);  //int(1) 返回受影响的记录数为1
```

* `SQL::id()` 或 `SQL::lastId()` 返回最后插入的一条记录的ID；

```php
$sql = 'insert into cds(titel, interpret, jahr ,id) values("Hello", "My friends", 2015, 6)';
SQL::update($sql);
$query = SQL::id();
echo $query;
```

* `SQL::errno()` 返回最后执行的一条SQL语句的出错号

```php
$sql = 'insert into cds(titel, interpret, jahr ,id) values("Hello", "My friends, 2015, 10)';//错误的SQL语句
SQL::update($sql);
echo SQL::errno();//输出错误号 1064
```

* `SQL::errmsg()` 返回最后执行的一条SQL语句的出错信息

```php
$sql = 'insert into cds(titel, interpret, jahr ,id) values("Hello", "My friends, 2015, 10)';//错误的SQL语句
SQL::update($sql);
echo SQL::errmsg();//输出错误信息 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '"My friends, 2015, 10)' at line 1
```

* `SQL::escape($str)` 返回安全的SQL字符串

```php
$escape="Da'Silva";          //放入要转义的字符串。
$r = SQL::escape($escape);   //编码的字符是 NUL（ASCII 0）、\n、\r、\、'、" 和 Control-Z。
echo $r;                     //输出Da\'Silva
$sql = "insert into cds(titel, interpret, jahr ,id) values('{$r}', 'My friends', 2015, 10)";
SQL::update($sql);           //不经过函数转义则报错
```

更简便的数据库操作：

* `SQL::getAll($table, $where)` 查询所有记录，其中$table是表名，$where是一个条件
数组，如：array('id' => 1)
```php
$prarm = array('id' => 4, 'jahr' => 2001);  //array(1) {[0] => array(4) {  
                                            //["titel"]     => string(33)"Goodbye Country (Hello Nightclub)" 
$query = SQL::getAll('cds', $prarm);        //["interpret"] => string(13)"Groove Armada" 
//var_dump($query);                         //["jahr"]      => string(4) "2001" 
echo $query2[0]['titel'];                   //["id"]        => string(1) "4"                              }}
                                            //输出 Goodbye Country (Hello Nightclub)
```
* `SQL::getOne($table, $where)` 查询一条记录

```php
$prarm = array('id' => 4, 'jahr' => 2001);
$query = SQL::getOne('cds', $prarm);
var_dump($query);
```

* `SQL::update($table, $where, $update)` 更新记录并返回受影响的记录数，其中
$update是要更新的数据数组，如：array('name' => 'haha')

更改前的表
<table>
<tr><td>Glee </td><td>Groove Armada</td><td>2001</td><td>4</td></tr>
</table>

更改后的表
<table>
<tr><td>Hello</td><td>My friends   </td><td>2015</td><td>2</td></tr>
</table>

```php
$prarm = array('titel' => 'Hello', 'interpret' => 'My friends', 'jahr' => '2015', 'id' => '2');
                                                  //$update带要更新值的数组
$prarm_where = array('id'=>'4');                  //$where 带判断值所在列的数组
$query = SQL::update('cds',$prarm_where,$prarm);  //id为4的列，里面的值全部被重新更新了一遍
```

* `SQL::insert($table, $data)` 插入一条记录并返回其ID，其中$data是一个数组，
如：array('name' => 'haha', 'age' => 20)

```php
$prarm = array('titel' => 'Hello', 'interpret' => 'My friends', 'jahr' => '2015', 'id' => '8');
$query = SQL::insert('cds',$prarm);  //插入了一个新的列
```

* `SQL::delete($table, $where)` 删除记录

```php
$prarm = array('id' => '8');
$query = SQL::delete('cds',$prarm);
```

条件格式：

* 普通：`array('a' => 1, 'b' => 2)` 相当于 `a=1 AND b=2`

* 指定连接操作符：`array('link' => 'OR', 'a' => 1, 'b' => 2)` 相当于 `a=1 OR b=2`

* 指定比较操作符：`array('a' => array('>' => 2))` 相当于 `a>2`

* 同一个字段多个条件：`array('a' => array('>' => 2, '<' => 5))` 相当于
`(a>2 AND a < 5)`

* 指定多个条件的连接操作符：`array('a' => array('link' => 'OR', '>' => 2, '<' => 5))` 
相当于 `(a>2 OR a < 5)`


上传文件操作
===============

slimphp中提供了一个静态类 __UPLOAD__ 来操作上传文件：

* `UPLOAD::get($filename)` 返回指定名称的上传文件信息，该名称为`<form>`表单中的
`<input type="file">`中的**name**值，该返回值为一个数组，包含以下项： __name__ 
（名称）， __type__ （MIME类型）， __size__ （大小）， 
__tmp_name__ （临时文件名）；

* `UPLOAD::move($file, $target)` 移动上传的文件到指定位置，第一个参数为
`UPLOAD::get($filename)`的返回值，第二个参数为目标文件名；


调试信息操作
=============

slimphp中提供了一个静态类 __DEBUG__ 来操作调试信息，当定义了常量`APP_DEBUG`时，
会在页面底部输出调试信息：

* `DEBUG::put($msg = '', $title = '')` 输出调试信息

<div>
<pre>
Debug Function method_get spent: 1ms 
Total spent: 3ms
<hr />
[2ms] [MySQL] Connected: root@127.0.0.1:3306 spent: 2ms
[2ms] [MySQL] charset=utf8
[2ms] [MySQL] Query: DELETE FROM `cds` WHERE `id`='8' spent: 0ms
[2ms] [MySQL] Close connection.
</pre>
</div>

* `DEBUG::get()` 取调试信息

* `DEBUG::clear()` 清除所有调试信息


应用相关操作
=============

slimphp中提供了一个静态类 __APP__ 来进行应用相关的操作，及一些公共函数：

* `APP::encryptPassword ($password)` 加密密码，返回一个加盐处理后的MD5字符串，
如：`FF:15855D447208A6AB4BD2CC88D4B91732:83`；

```php
$password = APP::encryptPassword('123456');
echo $password;
```

* `APP::validatePassword ($password, $encrypted)` 验证密码，第一个参数为待验证的
密码，第二个参数为`APP::encryptPassword ($password)`返回的字符串，
返回`TRUE`或`FALSE`；

```php
$password = APP::encryptPassword('123456');
$encrypt = APP::validatePassword('123456', $password);
var_dump($encrypt);  //bool(true)
```

* `APP::dump($var)` 打印变量结构，一般用于调试；

```php
$prarm = array('Hello', 'My friends', 2015, 2);  //Array( [0] => Hello  [1] => My friends
APP::dump($prarm);                               //       [2] => 2015   [3] => 2 )
```

* `APP::showError($msg)` 显示出错信息

<pre style='color: #900;
            font-size: 16px;
            border: 1px solid #900;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 12px 0px;'>连接失败</pre>

```php
$prarm = '连接失败';
APP::showError($prarm);
```

* `APP::load($filename)` 载入依赖的php文件，若不指定后缀名，会自动加上`.php`，
默认以当前php文件为根目录，若文件名以`/`开头，则以常量`APP_ROOT`定义的应用目录
作为根目录；

```php
APP::load('../mobile');        //引入mobile.php的手机类文件
var_dump(Mobile::isMobile());  //是手机访问则返回bool(true)
```

* `APP::sendJSON($data)` 返回JSON格式数据

* `APP::sendError($msg, $data = array())` 返回JSON格式的出错信息：`{"error":"msg"}`

* `APP::authEncode($string, $key, $expirey)` 加密账户验证信息，可指定过期时间

* `APP::authDecode($string, $key)` 加密账户验证信息

* `APP::getTemplate($name, $locals)` 载入模板文件，若不指定后缀名，会
自动加上`.html`，以常量`APP_TEMPLATE_ROOT`定义的模板目录作为根目录，模板文件实际
上为php程序文件，第二个参数为模板中可用的变量，在模板中通过`$locals`来读取（若
无命名冲突也可以直接使用键名），返回渲染后的内容

* `APP::setLocals($name, $value)` 设置模板变量

* `APP::getLocals($name)` 取模板变量值

* `APP::render($name, $locals, $layout = '')` 自动为`$locals`加上用
`APP::setLocals()`设置的变量，并渲染模板。如果指定了视图模板`$layout`，则需要在
视图模板中通过`$body`变量来获取模板内容。

* `APP::init()` 初始化slimphp；

* `APP::end()` 提前退出

自动路由
===========

slimphp中提供了一个静态类 __ROUTER__ 来进行路由相关的操作：

* `ROUTER::register($path, $function, $is_preg = false)` 注册中间件，其中`$path`
为路径前缀，`$function`为要执行的函数，如果`$is_preg`为`true`表示`$path`是一个
正则表达式

* `ROUTER::run($dir, $path)` 执行自动路由。其中`$dir`是要自动加载的PHP文件所在
的目录，以应用目录`APP_ROOT`中定义的目录为根目录，默认为`action`目录，`$path`是
当前请求的路径，默认为`$_GET['__path__']`

示例：

应用统一入口文件：index.php

```php
<?php
require('config.php');
ROUTER::run('action', @$_GET['__path__']);
?>
```

需要配置服务器的URL Rewrite，比如将 `/app/(.*)` 的所有请求转到
`/app/index.php?__path__=$1`

Apache的配置示例：

```
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^app/(.*)$ /app/index.php?%{QUERY_STRING}&__path__=$1 [L]
```

Nginx的配置示例：

```lua
if (!-e $request_filename) {
  rewrite "^/app/(.*)" "/app/index.php?%{QUERY_STRING}&__path__=$1" last;
}
```

SAE的配置示例：

```yaml
handle:
 - rewrite: if(!is_dir() && !is_file() && path~"^app/(.*)") goto "app/index.php?%{QUERY_STRING}&__path__=$1"
```

当请求 `/app/my/action` 时，会自动执行文件 `/action/my/action.php`

如请求 `/app/my/action/` ，则自动执行文件 `/action/my/action/index.php`
