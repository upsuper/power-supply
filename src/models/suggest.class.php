<?php

class Suggest
{
    private $suggest_id;
    private $email;
    private $content;
    private $time;
    private $reply_time;

    /**
     * 构造函数
     *
     * @param int $id 投诉建议 ID，如果为0则需要由调用者设置
     */
    public function __construct($id = 0)
    {
        if (! $id) return;
        global $db;
        $row = query_one($db, "
            SELECT `email`, `content`, `time`, `reply_time`
            FROM `suggests` WHERE `id`=%s
            ", $id);
        $this->suggest_id = $id;
        $this->email = $row['email'];
        $this->content = $row['content'];
        $this->time = $row['time'];
        $this->reply_time = $row['reply_time'];
    }

    /**
     * 创建新的投诉建议
     *
     * @param string $email 反馈邮箱
     * @param string $content
     * @return bool 是否成功
     */
    public static function create($email, $content)
    {
        global $db;
        $result = query($db, "
            INSERT INTO `suggests`
            (`email`, `content`) VALUES ('%s', '%s')
            ", $email, $content);
        $db->commit();
        return $result;
    }

    /**
     * 获取所有投诉建议
     *
     * @return array 投诉建议列表
     */
    public static function get_suggests()
    {
        global $db;
        $result = query($db, "
            SELECT `id`, `email`, `content`, `time`, `reply_time`
            FROM `suggests`");
        $ret = array();
        while ($row = $result->fetch_row()) {
            $suggest = new Suggest;
            list(
                $suggest->suggest_id,
                $suggest->email,
                $suggest->content,
                $suggest->time,
                $suggest->reply_time
            ) = $row;
            $ret[] = $suggest;
        }
        return $ret;
    }

    public function get_id()
    {
        return $this->suggest_id;
    }

    public function get_email()
    {
        return $this->email;
    }

    public function get_content()
    {
        return $this->content;
    }

    public function get_time()
    {
        return $this->time;
    }

    public function get_reply_time()
    {
        return $this->reply_time;
    }

    /**
     * 将投诉建议设置为已经回复
     */
    public function set_replied()
    {
        global $db;
        $result = query($db, "
            UPDATE `suggest` SET `reply_time`=NOW()
            WHERE `id`=%s
            ", $this->suggest_id);
        $db->commit();
    }
}

?>
