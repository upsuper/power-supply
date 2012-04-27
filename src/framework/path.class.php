<?php

/**
 * 管理路径
 */
class PathClass
{
    private $pos = 0;
    private $path = array();

    function __construct($path_info)
    {
        $this->path = explode('/', $path_info);
        $this->pos = 0;
    }

    /**
     * 获取下一个位置
     *
     * @param $default string 如果下一个位置为空则设置为该值并返回
     * @return string
     */
    function get_next($default = '')
    {
        $this->pos++;
        if (count($this->path) <= $this->pos)
            $next = '';
        else
            $next = $this->path[$this->pos];
        return $next;
    }

    /**
     * 根据给定路径获取完整路径
     * 如果给定路径为/开头，则认为是以脚本文件为根的
     * 绝对路径，如果不是，则认为是相对当前处理到的路径
     * 的相对路径。
     *
     * @param string $path 路径
     * @param bool $include_script optional 返回的路径是否包含脚本路径
     * @return string
     */
    function get_url($path, $include_script = true)
    {
        $ret = $this->path;
        $pos = $this->pos;
        array_splice($ret, $pos + 1);
        $path = explode('/', $path);
        if ($path[0] == '') {
            if (count($path) != 1) {
                $ret = array('');
                $pos = 0;
            }
        }
        foreach ($path as $name) {
            if ($name == '..') {
                array_splice($ret, $pos);
                --$pos;
                if ($pos <= 0)
                    $pos = 1;
            } else {
                if ($name != '' && $name != '.')
                    $ret[$pos] = $name;
                ++$pos;
            }
        }
        $ret[] = '';
        $ret = implode('/', $ret);
        if ($include_script)
            $ret = SCRIPT_URI.$ret;
        return $ret;
    }
}

?>
