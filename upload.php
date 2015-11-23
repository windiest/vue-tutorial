<?php
/**
 * 上传文件管理
 */
if (!class_exists('UPLOAD', false)) {
  class UPLOAD {
    /**
     * 获取上传文件
     * 返回格式：{name: 名称, type: 文件MIME类型, size: 大小, tmp_name: 临时文件名}
     *
     * @param string $filename
     * @return array
     */
    public static function get ($filename) {
      if (isset($_FILES[$filename])) {
        $uploaded_file = array(
          'name' => $_FILES[$filename]['name'],
          'type' => $_FILES[$filename]['type'],
          'size' => $_FILES[$filename]['size'],
          'tmp_name' => $_FILES[$filename]['tmp_name']
          );
      } elseif (isset($_POST[$filename])) {
        $uploaded_file = array(
          'name' => $_POST[$filename],
          );
      } elseif (isset($GLOBALS['HTTP_POST_FILES'][$filename])) {
        global $HTTP_POST_FILES;
        $uploaded_file = array(
          'name' => $HTTP_POST_FILES[$filename]['name'],
          'type' => $HTTP_POST_FILES[$filename]['type'],
          'size' => $HTTP_POST_FILES[$filename]['size'],
          'tmp_name' => $HTTP_POST_FILES[$filename]['tmp_name']
          );
      } elseif (isset($GLOBALS['HTTP_POST_VARS'][$filename])) {
        global $HTTP_POST_VARS;
        $uploaded_file = array(
          'name' => $HTTP_POST_VARS[$filename],
          );
      } else {
        $uploaded_file = array(
          'name' => $GLOBALS[$filename . '_name'],
          'type' => $GLOBALS[$filename . '_type'],
          'size' => $GLOBALS[$filename . '_size'],
          'tmp_name' => $GLOBALS[$filename]
        );
      }
      return $uploaded_file;
    }

    /**
     * 移动临时文件
     *
     * @param {array} $file
     * @param {string} $target
     * @return {string}
     */
    public static function move ($file, $target) {
      $timestamp = microtime(true);
      $source = is_array($file) ? $file['tmp_name'] : $file;
      move_uploaded_file($source, $target);
      DEBUG::put('Move '.$source.' to '.$target.' spent: '.round((microtime(true) - $timestamp) * 1000, 3).'ms', 'Upload');
      return $target;
    }
  }
} else {
  DEBUG::put('Class UPLOAD is already exists!', 'Warning');
}
?>