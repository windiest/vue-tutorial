<?php
/**
 * 调试信息流操作
 */
if (!class_exists('DEBUG', false)) {
  class DEBUG {
    public static $stack = '';

    /**
     * 添加到DEBUG流
     *
     * @param string $msg
     * @param string $title
     */
    public static function put ($msg = '', $title = '') {
      if (APP::$is_debug) {
      	//empty()函数是用来测试变量是否已经配置。若变量已存在、非空字符串或者非零，则返回 false 值；
      	//反之返回 true值。所以，当字符串的值为0时，也返回true，就是执行empty内部的语句。这就是陷阱。
        if (!empty($title)) {
          $msg = "[$title] $msg";
        }
        $timestamp = round((microtime(true) - APP_TIMESTAMP_START) * 1000, 3).'ms';
        DEBUG::$stack .= "[$timestamp] $msg\r\n";
      }
    }

    /**
     * 获取DEBUG流
     *
     * @return string
     */
    public static function get () {
      return DEBUG::$stack;
    }

    /**
     * 清空DEBUG流，并返回之前的信息
     *
     * @return string
     */
    public static function clear () {
      $ret = DEBUG::$stack;
      DEBUG::$stack = '';
      return $ret;
    }
  }
}

?>