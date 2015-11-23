<?php

if (!class_exists('ROUTER', false)) {
  class ROUTER {

    // 中间件列表
    public static $_list = array();

    /**
     * 开始自动路由
     *
     * @param string $dir  目录，默认为 action 表示应用目录下的action目录
     * @param string $path 路径，默认使用 $_GET['__path__']，如为空则为 /
     */
    public static function run ($dir = 'action', $path = '_____NORMAL_____') {
      // 目录不能以/开头及结尾
      if (substr($dir, 0, 1) == '/') $dir = substr($dir, 1);
      if (substr($dir, -1) == '/') $dir = substr($dir, strlen($dir) - 1);
      // 路径必须以/开头，但不能以/结尾
      if ($path == '_____NORMAL_____') $path = @$_GET['__path__'];
      if (empty($path)) $path = '/';
      if (substr($path, 0, 1) != '/') $path = '/'.$path;
      if ($path != '/' && substr($path, -1) == '/') $path = substr($path, strlen($path) - 1);

      // 中间件处理
      ROUTER::runMiddleware($path);

      $filename = APP_ROOT.$dir.$path.(substr($path, -1) == '/' ? '/index' : '').'.php';
      DEBUG::put("path=$path, file=$filename", 'Router');
      if (file_exists($filename)) {
        require($filename);
      } else {
        ROUTER::notFound($path, $filename);
      }
    }

    /**
     * 路由未找到
     */
    public static function notFound ($path, $filename) {
      @header("HTTP/1.1 404 Not Found");
      APP::showError("Path \"$path\" Not Found.");
      DEBUG::put("Not found: path=$path, file=$filename", 'Router');
    }

    /**
     * 注册中间件
     *
     * @param string $path        路径
     * @param callback $function  要执行的函数
     * @param bool $is_preg       路径是否为正则表达式，默认为false
     */
    public static function register ($path, $function, $is_preg = false) {
      ROUTER::$_list[] = array($path, $function, $is_preg);
      DEBUG::put("Use: $path => $function", 'Router');
    }

    /**
     * 执行中间件
     *
     * @param string $path
     */
    public static function runMiddleware ($path) {
      $pathlen = strlen($path);
      foreach (ROUTER::$_list as $i => $v) {
        $p = $v[0];
        $f = $v[1];
        $is_preg = $v[2];
        if ($is_preg) {
          if (!preg_match($p, $path)) continue;
        } else {
          $pl = strlen($p);
          if ($pl > $pathlen) continue;
          if (substr($path, 0, $pl) != $p) continue;
        }
        @call_user_func($f, $path);
      }
    }
  }
} else {
  DEBUG::put('Class ROUTER is already exists!', 'Warning');
}

?>