<?php
/**
 * MySQL 数据库操作
 */
 
//class_exists ( string $class_name [, bool $autoload ] )
//如果由 class_name 所指的类已经定义，此函数返回 TRUE，否则返回 FALSE。
if (!class_exists('SQL', false)) {
  //不存在SQL类则运行下面代码
  class SQL {

    // 当前数据库连接
    public static $connection = null;

    /**
     * 连接到数据库
     * 成功返回true, 失败返回false
     *
     * @param string $server
     * @param string $username
     * @param string $password
     * @param string $database
     * @return bool
     */
     
    //如果$server和$username带有参数，则用参数的值， 否则用默认值$server = 'localhost:3306'和$username = 'root'
    public static function connect ($server = 'localhost:3306', $username = 'root', $password = '', $database = '') {
      //microtime(get_as_float)
      //参数	描述
      //get_as_float 如果给出了 get_as_float 参数并且其值等价于 TRUE，该函数将返回一个浮点数。
      $timestamp = microtime(true);

      SQL::$connection = mysqli_connect($server, $username, $password, $database);
	  
	  //mysqli_connect(host,username,password,dbname,port,socket);

      //参数	描述
      //host	可选。规定主机名或 IP 地址。
      //username	可选。规定 MySQL 用户名。
      //password	可选。规定 MySQL 密码。
      //dbname	可选。规定默认使用的数据库。
      //port	可选。规定尝试连接到 MySQL 服务器的端口号。
      //socket	可选。规定 socket 或要使用的已命名 pipe。
	  
	  //round(x,prec)
      //参数	描述
      //x	可选。规定要舍入的数字。
      //prec	可选。规定小数点后的位数。
      //说明
      //返回将 x 根据指定精度 prec （十进制小数点后数字的数目）进行四舍五入的结果。prec 也可以是负数或零（默认值）。

      DEBUG::put('Connected: '.$username.'@'.$server.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'MySQL');

      $err = SQL::error();
      if ($err['id'] > 0) {
        DEBUG::put('  - Error: #'.$err['id'].' '.$err['error'], 'MySQL');
      }

      return SQL::$connection;
    }

    /**
     * 获取出错信息
     * 返回数据格式：  {id:出错ID, error:出错描述}
     *
     * @return array
     */
    public static function error () {
      return array(
        'id'  =>    SQL::errno(),
        'error' =>  SQL::errmsg()
        );
    }

    /**
     * 返回出错代码
     *
     * @return int
     */
    public static function errno () {
      return mysqli_errno(SQL::$connection);
    }

    /**
     * 返回出错描述信息
     *
     * @return string
     */
    public static function errmsg () {
      return mysqli_error(SQL::$connection);
    }

    /**
     * 设置字符编码
     *
     * @param {String} $encoding
     * @return {String}
     */
    public static function charset ($encoding = '') {
      if (!empty($encoding)) {
        mysqli_set_charset(SQL::$connection, $encoding);
        DEBUG::put('charset='.$encoding, 'MySQL');
      }
      return mysqli_get_charset(SQL::$connection);
    }

    /**
     * 执行SQL语句
     *
     * @param string $sql
     * @return resource
     */
    public static function query ($sql) {
      $timestamp = microtime(true);
      $r = mysqli_query(SQL::$connection, $sql);
      $spent = round((microtime(true) - $timestamp) * 1000, 3);
      if ($r) {
        DEBUG::put('Query: '.$sql.' spent: '.$spent.'ms', 'MySQL');
      } else {
        DEBUG::put('Query: '.$sql.' fail: #'.SQL::errno().' '.SQL::errmsg().' spent: '.$spent, 'MySQL');
      }
      return $r;
    }

    /**
     * 查询并返回所有数据
     * 格式为： [{字段名:值, 字段名:值 ...}, ...]，返回false表示失败
     *
     * @param string $sql
     * @return array
     */
    public static function getAll ($sql, $where = null) {
      //is_array ( mixed $var )
      //如果 var 是 array，则返回 TRUE，否则返回 FALSE。
      if (is_array($where)) return SQL::getAll2($sql, $where);

      $r = SQL::query($sql);
      if (!$r) return false;
      $data = array();
      while ($row = mysqli_fetch_array($r, MYSQL_ASSOC)) {
        $data[] = $row;
      }
      return count($data) < 1 ? false : $data;
    }
    public static function getData ($sql) {
      return SQL::getAll($sql);
    }

    /**
     * 查询并返回一行数据 格式为 {字段名:值, 字段名:值 ...}，返回false表示失败
     *
     * @param string $sql
     * @return array
     */
    public static function getOne ($sql, $where = null) {
      if (is_array($where)) return SQL::getOne2($sql, $where);

      $sql .= ' LIMIT 1';
      $data = SQL::getAll($sql);
      return $data == false ? false : $data[0];
    }
    public static function getLine ($sql) {
      return SQL::getOne($sql);
    }

    /**
     * 执行SQL命令 返回影响的记录行数
     *
     * @param string $sql
     * @return int
     */
    public static function update ($sql, $where = null, $update = null) {
      if (is_array($where) && is_array($update)) return SQL::update2($sql, $where, $update);

      $r = SQL::query($sql);
      if (!$r) return false;
      return mysqli_affected_rows(SQL::$connection);
    }
    public static function runSql ($sql) {
      return SQL::update($sql);
    }

    /**
     * 插入记录
     *
     * @param string $table
     * @param array $data
     * @return int
     */
    public static function insert ($table, $data) {
      if (!(is_array($data) && count($data) > 0)) return false;

      $table = SQL::escape($table);
      $fields = array();
      $values = array();
      foreach ($data as $f => $v) {
        $fields[] = '`'.SQL::escape($f).'`';
        $values[] = '\''.SQL::escape($v).'\'';
      }
      $fields = implode(', ', $fields);
      $values = implode(', ', $values);
      $sql = "INSERT INTO `$table` ($fields) VALUES ($values)";
      return SQL::update($sql) > 0 ? SQL::id() : false;
    }

    /**
     * 解析where条件
     *
     * @param array $where 例如： array(
     *                              'field' => 'values',
     *                              'link' =>  'OR'  // 可省略，默认为AND
     *                            )
     *                            array('field' => array(
     *                              'link' => 'OR', // 可省略，默认为AND
     *                              '>' =>    1200,
     *                              '<=' =>   555
     *                            ))
     * @return string
     */
    public static function _parseWhere ($where) {
      if (count($where) < 1) return '1';

      $items = array();
      $link = 'AND';
      foreach ($where as $f => $v) {
        if (strtolower($f) == 'link') {
          $link = strtoupper($v);
          continue;
        }
        $f = SQL::escape($f);
        if (is_array($v)) {
          $items2 = array();
          $link2 = 'AND';
          foreach ($v as $op1 => $op2) {
            if (strtolower($op1) == 'link') {
              $link2 = strtoupper($op2);
              continue;
            }
            $op2 = SQL::escape($op2);
            $items2[] = "`$f`$op1'$op2'";
          }
          $items[] = '('.implode(" $link2 ", $items2).')';
        } else {
          $v = SQL::escape($v);
          $items[] = "`$f`='$v'";
        }
      }
      return implode(" $link ", $items);
    }

    /**
     * 更新记录
     *
     * @param string $table
     * @param array $where
     * @param array $update
     * @return int
     */
    public static function update2 ($table, $where, $update) {
      if (!(is_array($where) && count($where) > 0 && is_array($update) && count($update) > 0)) return false;

      $table = SQL::escape($table);
      $set = array();
      foreach ($update as $f => $v) {
        $f = SQL::escape($f);
        $v = SQL::escape($v);
        $set[] = "`$f`='$v'";
      }
      $set = implode(', ', $set);
      $where = SQL::_parseWhere($where);
      $sql = "UPDATE `$table` SET $set WHERE $where";
      return SQL::update($sql);
    }

    /**
     * 删除记录
     *
     * @param string $table
     * @param array $where
     * @return int
     */
    public static function delete ($table, $where) {
      if (!is_array($where) && count($where) > 0) return false;

      $table = SQL::escape($table);
      $where = SQL::_parseWhere($where);
      $sql = "DELETE FROM `$table` WHERE $where";
      return SQL::update($sql);
    }

    /**
     * 查询一条记录
     *
     * string $table
     * @param array $where
     * @return array
     */
    public static function getOne2 ($table, $where) {
      if (!is_array($where) && count($where) > 0) return false;

      $table = SQL::escape($table);
      $where = SQL::_parseWhere($where);
      $sql = "SELECT * FROM `$table` WHERE $where";
      return SQL::getOne($sql);
    }

    /**
     * 查询记录
     *
     * string $table
     * @param array $where
     * @return array
     */
    public static function getAll2 ($table, $where) {
      if (!is_array($where) && count($where) > 0) return false;

      $table = SQL::escape($table);
      $where = SQL::_parseWhere($where);
      $sql = "SELECT * FROM `$table` WHERE $where";
      return SQL::getAll($sql);
    }

    /**
     * 取最后插入ID
     *
     * @return int
     */
    public static function id () {
      return mysqli_insert_id(SQL::$connection);
    }
    public static function lastId () {
      return SQL::id();
    }

    /**
     * 转换为SQL安全字符串
     *
     * @param string $str
     * @return string
     */
    public static function escape ($str) {
      return  mysqli_real_escape_string(SQL::$connection, $str);
    }

    /**
     * 关闭SQL连接
     */
    public static function close () {
      DEBUG::put('Close connection.', 'MySQL');
      return @mysqli_close(SQL::$connection);
    }
  }
} else {
  DEBUG::put('Class SQL is already exists!', 'Warning');
}
?>