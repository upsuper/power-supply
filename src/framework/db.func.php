<?php

/**
 * 执行数据库查询
 *
 * 使用 printf 风格的字符串格式化，函数会自动对其中的参数进行处理
 *
 * @param mysqli $db
 * @param string $sql
 * @param ... 
 * @return mixed
 */
function query($db, $sql)
{
    $args = func_get_args();
    $args = array_slice($args, 2);
    $args = array_map(array($db, 'real_escape_string'), $args);
    $sql = vsprintf($sql, $args);
    $ret = $db->query($sql);
    return $ret;
}

/**
 * 执行数据库查询并获取第一行返回
 *
 * @param mysqli $db
 * @param string $sql
 * @param ... 
 * @return mixed
 */
function query_one($db, $sql)
{
    $result = call_user_func_array('query', func_get_args());
    if (! $result instanceof MySQLi_Result)
        return $result;
    $row = $result->fetch_assoc();
    $result->close();
    return $row;
}

?>
