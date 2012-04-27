<?php

/**
 * 发送状态头部
 *
 * @param string $header
 */
function status_header($header)
{
    header($_SERVER['SERVER_PROTOCOL'].' '.$header);
    header('Status: '.$header);
}

/**
 * 获取相对网站根的绝对路径
 *
 * @param string $path
 * @return string
 */
function abs_url($path)
{
    return SCRIPT_URI.$path;
}

/**
 * 从定向到指定路径，
 * 如果以 / 开头则重定向到相对 index.php/ 为根的位置，
 * 否则直接重定向。
 * 此函数不返回。
 * 
 * @param string $to_path 可选，转移到的路径
 * @param bool $permanent 可选，如果为 true 则设置永久重定向
 */
function redirect($to_path = '.', $permanent = false)
{
    global $PATH;
    if ($to_path[0] == '/')
        $to_path = abs_url($to_path);
    if ($permanent)
        status_header('301 Moved Permanently');
    header('Location: '.$to_path);
    exit();
}

/**
 * 返回 404 Not Found，函数不返回
 */
function not_found()
{
    status_header('404 Not Found');
    echo '<h1>Not Found</h1>';
    exit();
}

/**
 * 返回 403 Forbidden，函数不返回
 */
function forbidden()
{
    status_header('403 Forbidden');
    echo '<h1>Forbidden</h1>';
    exit();
}

/**
 * 添加闪现消息
 *
 * @param string $msg
 * @param string $type 类型，可为
 *              success、fail、error、info、warn
 */
function flash($msg, $type)
{
    $_SESSION['flash'][] = array($msg, $type);
}

/**
 * 获取一个提交代号
 * 随机生成的会话级字串，用于防止跨站攻击。
 * 在同一个会话的任何时候调用此函数会返回一致的内容。
 *
 * @return string
 */
function get_token()
{
    if (! isset($_SESSION['token'])) {
        $token = pack('LLL', mt_rand(), mt_rand(), mt_rand());
        $_SESSION['token'] = strtr(base64_encode($token), '+/', '_-');
    }
    return $_SESSION['token'];
}

/**
 * 应用模板
 *
 * @param string $tpl 模板名称
 * @param array $data 模板数据
 */
function template($tpl, $data = array())
{
    function view_autoload($classname)
    {
        if (substr($classname, -5) != '_View')
            return;
        $filename = substr($classname, 0, -5);
        $filename = preg_replace('/[A-Z][0-9a-z]*/', '/\0', $filename);
        $filename = VIEW_PATH.strtolower($filename);
        if (file_exists($filename.'/__base.php')) {
            include($filename.'/__base.php');
        } elseif (file_exists($filename.'.php')) {
            include($filename.'.php');
        }
    }
    spl_autoload_register('view_autoload');

    require_once(FRAME_PATH.'/view.class.php');
    $tplname = $tpl.'_View';
    if (! class_exists($tplname)) {
        echo "View \"$tpl\" not found.";
        return;
    }
    $inst = new $tplname($data);
    echo $inst->render();
}

?>
