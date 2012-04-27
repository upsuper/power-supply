<?php

// 加载配置
require_once(CONFIG_FILE);

// 加载库
require_once(FRAME_PATH.'/db.func.php');
require_once(FRAME_PATH.'/mail.func.php');
require_once(FRAME_PATH.'/path.class.php');
require_once(FRAME_PATH.'/request.func.php');
require_once(FRAME_PATH.'/response.func.php');
require_once(FRAME_PATH.'/controller.class.php');

// 初始化数据库
$db = new mysqli($CONFIG['db']['hostname'],
                 $CONFIG['db']['username'],
                 $CONFIG['db']['password'],
                 $CONFIG['db']['database']);
$db->autocommit(false);
$db->set_charset('utf8');

// 清理输入信息
function t_stripslashes_deep($value)
{
    return is_array($value) ?
        array_map('t_stripslashes_deep', $value) : stripslashes($value);
}
if (get_magic_quotes_gpc()) {
    $_GET    = t_stripslashes_deep($_GET);
    $_POST   = t_stripslashes_deep($_POST);
    $_COOKIE = t_stripslashes_deep($_COOKIE);
}

// 注册模块的自动载入
function model_autoload($classname)
{
    if (substr($classname, -11) == '_Controller')
        return;
    if (substr($classname, -5) == '_View')
        return;

    $filename = preg_replace('/(?<=[0-9a-z])[A-Z]|[A-Z](?=[a-z])/',
        '_\0', $classname);
    if ($filename[0] == '_')
        $filename = substr($filename, 1);
    $filename = strtolower($filename);
    $filename = str_replace('__', '/', $filename);
    $filename = MODEL_PATH.'/'.$filename.'.class.php';
    file_exists($filename) and include($filename);
}
spl_autoload_register('model_autoload');

// 注册控制器的自动载入
// TODO

// 初始化 PHP 信息
session_start();
mb_internal_encoding('UTF-8');
date_default_timezone_set('Asia/Shanghai');

// 获取地址
if (! isset($_SERVER['PATH_INFO']) || ! $_SERVER['PATH_INFO'])
    $_SERVER['PATH_INFO'] = '/';
$path = explode('/', $_SERVER['PATH_INFO']);
if (end($path) != '')
    redirect(implode('/', $path).'/');
$orig_len = count($path);
function filter_path($var)
{
    return $var && $var != '.' && $var != '..';
}
$path = array_filter($path, 'filter_path');
while (end($path) == 'index')
    array_pop($path);
if (count($path) != $orig_len - 2)
    redirect('/'.implode('/', $path).'/');

// 转入控制器
include(CTRL_PATH.'/__init.php');
$inst = new _Controller;
$first = array_shift($path);
$resp = $inst->__call($first, $path);

// 返回数据
// TODO 处理返回数据而不是让他们直接显示

?>
