<?php

/**
 * 安全获取 $_POST 内的信息
 *
 * @param string $name
 * @return mixed
 */
function get_form($name)
{
    return isset($_POST[$name]) ? $_POST[$name] : null;
}

?>
