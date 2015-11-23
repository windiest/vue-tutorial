<?php
/**
 * SlimPHP
 *
 * @author
 * @version 0.3
 */

/* 处理不同的请求方法 */
function _slimphp_request_method_router () {
  // 如果已调用APP::end()，则不再执行此函数，因为在die后仍然会执行register_shutdown_function注册的函数
  if (APP::$is_exit) return;

  // 执行相应的请求方法
  // strtolower(string)
  // 参数	描述
  // string	必需。规定要转换的字符串。
  // 技术细节
  // 返回值：	返回转换为小写的字符串。
  // $_SERVER['REQUEST_METHOD'] #访问页面时的请求方法。例如：“GET”、“HEAD”，“POST”，“PUT”。
  $method = strtolower($_SERVER['REQUEST_METHOD']);
  //得到是get或者post然后下面拼接method_get或者method_post
  $funcname = "method_$method";
  //microtime() 函数返回当前 Unix 时间戳和微秒数。
  define('APP_TIMESTAMP_ROUTE', microtime(true));
  if (function_exists($funcname)) {
    $funcname();
  } elseif (function_exists('method_all')) {
    $funcname = 'method_all';
    method_all();
  } else {
    $funcname = 'method_undefine';
  }

  // 关闭数据库连接
  @SQL::close();

  // 显示调试信息
  // $_SERVER['HTTP_ACCEPT'] #当前请求的 Accept: 头部的内容。
  $accept_type = strtolower(trim($_SERVER['HTTP_ACCEPT']));
  //substr(string,start,length)
  //参数	描述
  //string	必需。规定要返回其中一部分的字符串。
  //start	
  //必需。规定在字符串的何处开始。
  //正数 - 在字符串的指定位置开始
  //负数 - 在从字符串结尾开始的指定位置开始
  //0 - 在字符串中的第一个字符处开始
  //length	
  //可选。规定被返回字符串的长度。默认是直到字符串的结尾。
  //正数 - 从 start 参数所在的位置返回的长度
  //负数 - 从字符串末端返回的长度
  if (APP::$is_debug && substr($accept_type, 0, 9) == 'text/html') {
  	//APP_TIMESTAMP_ROUTE  25行
    $spent2 = round((microtime(true) - APP_TIMESTAMP_ROUTE) * 1000, 3);
    $spent = round((microtime(true) - APP_TIMESTAMP_START) * 1000, 3);
    $debug = DEBUG::clear();
    echo "<div style='
      font-size: 14px;
      line-height: 1.6em;
      text-align: left;
      color: #000;
      padding: 12px 8px;
      border: 1px solid #DDD;
      font-family: \"Microsoft yahei\", \"Helvetica Neue\", \"Lucida Grande\", \"Lucida Sans Unicode\", Helvetica, Arial, sans-serif !important;
      background-color: #EEE;
      margin-top: 50px;
'>Debug<br>Function $funcname spent: {$spent2}ms<br>Total spent: {$spent}ms<br>
<hr><pre style='
      font-family: \"Microsoft yahei\", \"Helvetica Neue\", \"Lucida Grande\", \"Lucida Sans Unicode\", Helvetica, Arial, sans-serif !important;
'>$debug</pre>
</div>";
  }
}


class APP {

  // 版本号
  public static $version = 1;

  // 是否提前退出
  public static $is_exit = false;

  // 是否为调试状态
  public static $is_debug = false;

  // 模板变量
  public static $locals = array();

  /**
   * 账户验证加密解密函数 （authcode函数）
   *
   * @param string $string 明文 或 密文
   * @param string $operation DECODE表示解密,其它表示加密
   * @param string $key 密匙
   * @param int $expiry 密文有效期
   *
   * @return string
   */
  public static function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
	/*md5(string,raw)
             参数	描述
     string	必需。规定要计算的字符串。
     raw	
            可选。规定十六进制或二进制输出格式：
     TRUE - 原始 16 字符二进制格式
     FALSE - 默认。32 字符十六进制数
           技术细节
           返回值：	如果成功则返回已计算的 MD5 散列，如果失败则返回 FALSE。
     PHP 版本：	4+
           更新日志：	在 PHP 5.0 中，raw 参数变为可选的。
	 * */
    $key = md5($key ? $key : 'leiphp-default-key');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for($i = 0; $i <= 255; $i++) {
      $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for($j = $i = 0; $i < 256; $i++) {
      $j = ($j + $box[$i] + $rndkey[$i]) % 256;
      $tmp = $box[$i];
      $box[$i] = $box[$j];
      $box[$j] = $tmp;
    }
    for($a = $j = $i = 0; $i < $string_length; $i++) {
      $a = ($a + 1) % 256;
      $j = ($j + $box[$a]) % 256;
      $tmp = $box[$a];
      $box[$a] = $box[$j];
      $box[$j] = $tmp;
      $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if($operation == 'DECODE') {
      if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
        return substr($result, 26);
      } else {
        return '';
      }
    } else {
      return $keyc.str_replace('=', '', base64_encode($result));
    }
  }

  /**
   * 加密账户验证信息
   *
   * @param string $string  要加密的字符串
   * @param string $key     密匙
   * @param int $expiry     从现在起的有效时间（秒）
   * @return string
   */
  public static function authEncode ($string, $key, $expiry = 0) {
    return APP::authcode($string, 'ENCODE', $key, $expiry);
  }

  /**
   * 解密账户验证信息
   *
   * @param string $string  密文
   * @param string $key     密匙
   * @return string
   */
  public static function authDecode ($string, $key) {
    return APP::authcode($string, 'DECODE', $key);
  }

  /**
   * 加密密码
   *
   * @param string $password
   * @return string
   */
  public static function encryptPassword ($password) {
    $random = strtoupper(md5(rand().rand()));
    $left = substr($random, 0, 2);
    $right = substr($random, -2);
    $newpassword = strtoupper(md5($left.$password.$right));
    return $left.':'.$newpassword.':'.$right;
  }

  /**
   * 验证密码
   *
   * @param string $password 待验证的密码
   * @param string $encrypted 密码加密字符串
   * @return bool
   */
  public static function validatePassword ($password, $encrypted) {
    $random = explode(':', strtoupper($encrypted));
    if (count($random) < 3) return false;
    $left = $random[0];
    $right = $random[2];
    $main = $random[1];
    $newpassword = strtoupper(md5($left.$password.$right));
    return $newpassword == $main;
  }

  /**
   * 显示出错信息
   *
   * @param string $msg
   */
  public static function showError ($msg) {
    $accept_type = strtolower(trim($_SERVER['HTTP_ACCEPT']));
    if (strpos($accept_type, 'json') !== false) {
      APP::sendJSON(array('error' => $msg));
    } else {
      echo "<div style='color: #900;
            font-size: 16px;
            border: 1px solid #900;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 12px 0px;'>$msg</div>";
    }
  }

  /**
   * 载入模板
   * 如果指定了参数$layout，则会嵌套一个layout模板
   *
   * @param string $name   模板名
   * @param array $locals  变量
   * @return string
   */
  public static function getTemplate ($name, $locals = array()) {
    if (!pathinfo($name, PATHINFO_EXTENSION)) $name = $name.'.html';
    $filename = APP_TEMPLATE_ROOT.$name;
    $timestamp = microtime(true);
    ob_start();
    extract($locals, EXTR_SKIP);
    include($filename);
    $html = ob_get_clean();
    DEBUG::put('Render '.$filename.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Template');
    return $html;
  }

  /**
   * 渲染模板，自动使用APP::$locals中的数据
   * 如果指定了参数$layout，则会嵌套一个layout模板
   *
   * Examples:
   * APP::render('template');
   * APP::render('template', $locals);
   * APP::render('template', 'layout');
   * APP::render('template', $locals, 'layout');
   *
   * @param string $name
   * @param array $locals
   * @param string $layout
   */
  public static function render ($name, $locals = array(), $layout = '') {
    if (!is_array($locals)) {
      $layout = $locals;
      $locals = array();
    }

    foreach (APP::$locals as $i => $v) {
      if (!isset($locals[$i])) $locals[$i] = $v;
    }

    $body = APP::getTemplate($name, $locals);
    if (empty($layout)) {
      echo $body;
    } else {
      $locals['body'] = $body;
      echo APP::getTemplate($layout, $locals);
    }
  }

  public static function template ($name, $locals = array(), $layout = '') {
    APP::render($name, $locals, $layout);
  }

  /**
   * 设置模板变量
   *
   * @param string $name
   * @param mixed $value
   * @return mixed
   */
  public static function setLocals ($name, $value = null) {
    APP::$locals[$name] = $value;
    return @APP::$locals[$name];
  }

  /**
   * 取模板变量
   *
   * @param string $name
   * @return mixed
   */
  public static function getLocals ($name) {
    return @APP::$locals[$name];
  }

  /**
   * 返回JSON格式数据
   *
   * @param mixed $data
   */
  public static function sendJSON ($data = null) {
    @header('content-type: application/json');
    if (is_array($data) && APP::$is_debug) $data['debug'] = DEBUG::get();
    echo json_encode($data);
    APP::end();
  }

  /**
   * 返回JSON格式的出错信息
   *
   * @param string $msg   出错信息
   * @param array $data   其他数据
   */
  public static function sendError ($msg, $data = array()) {
    $data['error'] = $msg;
    APP::sendJSON($data);
  }

  /**
   * 加载文件
   * 文件名如果不指定扩展名，则自动加上.php再加载
   * 如果以 / 开头，则从应用根目录开始查找
   *
   * @param string $filename
   * @return mixed
   */
  public static function load ($filename) {
    if (!pathinfo($filename, PATHINFO_EXTENSION)) {
      $filename = $filename.'.php';
    }
    if (substr($filename, 0, 1) == '/') {
      $filename = APP_ROOT.substr($filename, 1);
    } else {
      $filename = dirname($_SERVER["SCRIPT_FILENAME"]).'/'.$filename;
    }
    return require($filename);
  }

  /**
   * 调试输出
   *
   * @param mixed $var
   */
  public static function dump ($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
  }

  /**
   * 初始化
   */
  public static function init () {
    // 是否关闭出错显示
    if (defined('APP_DEBUG') && APP_DEBUG) {
      APP::$is_debug = true;
      error_reporting(E_ALL);
      ini_set('display_errors', '1');
    } else {
      error_reporting(0);
      ini_set('display_errors', '0');
    }

    // 开始时间
    define('APP_TIMESTAMP_START', microtime(true));

    // 只要定义了数据库配置中的任一项均自动连接数据库
    // defined() 函数检查某常量是否存在。
    if (defined('CONF_MYSQL_SERVER') || defined('CONF_MYSQL_USER') ||
        defined('CONF_MYSQL_PASSWD') || defined('CONF_MYSQL_DBNAME')) {
      $server = defined('CONF_MYSQL_SERVER') ? CONF_MYSQL_SERVER : 'localhost:3306';
      $user = defined('CONF_MYSQL_USER') ? CONF_MYSQL_USER : 'root';
      $passwd = defined('CONF_MYSQL_PASSWD') ? CONF_MYSQL_PASSWD : '';
      $dbname = defined('CONF_MYSQL_DBNAME') ? CONF_MYSQL_DBNAME : '';
      $permanent = defined('CONF_MYSQL_PERMANENT') ? CONF_MYSQL_PERMANENT : false;
      SQL::connect($server, $user, $passwd, $dbname, $permanent);
      if (defined('CONF_MYSQL_CHARSET')) SQL::charset(CONF_MYSQL_CHARSET);
    }

    // 自动执行 method_VERB
    //register_shutdown_function 的函数,可以让我们设置一个当执行关闭时可以被调用的另一个函数.
    //也就是说当我们的脚本执行完成或意外死掉导致PHP执行即将关闭时,我们的这个函数将会 被调用.
    register_shutdown_function('_slimphp_request_method_router');
  }

  /**
   * 提前退出
   */
  public static function end () {
    APP::$is_exit = true;
	//die() 函数输出一条消息，并退出当前脚本。
    //该函数是 exit() 函数的别名。
    die;
  }
}
