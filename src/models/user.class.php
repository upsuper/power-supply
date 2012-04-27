<?php

class User
{
    private $user_id;
    private $email;
    private $password;
    private $room;
    private $points;

    /**
     * 构造函数
     *
     * @param string $user_id 用户 ID
     */
    public function __construct($user_id)
    {
        global $db;
        $user = query_one($db, "
            SELECT `email`, `password`, `room_id`, `points`
            FROM `users` WHERE `id`=%s
            ", $user_id);
        $this->user_id = $user_id;
        $this->email = $user['email'];
        $this->password = $user['password'];
        if ($user['room_id'])
            $this->room = Room::get_room_by_id($user['room_id']);
        else
            $this->room = null;
        $this->points = $user['points'];
    }

    /**
     * 获取用户实例，未找到则返回 false
     *
     * @param string $email 登入 Email
     * @return User|false
     */
    public static function get_user($email)
    {
        global $db;
        $user = query_one($db, "
            SELECT `id` FROM `users` WHERE `email`='%s'
            ", $email);
        if (! $user)
            return false;
        return new User($user['id']);
    }

    /**
     * 添加用户
     *
     * @param string $email
     * @return User|false 添加成功返回用户实例，否则返回 false
     */
    public static function add_user($email)
    {
        global $db;
        $result = query($db, "
            INSERT INTO `users` 
            (`email`) VALUES ('%s')
            ", $email);
        if (! $result)
            return false;
        $id = $db->insert_id;
        $db->commit();
        return new User($id);
    }

    /**
     * 获取用户 ID
     *
     * @return int
     */
    public function get_id()
    {
        return $this->user_id;
    }

    /**
     * 获取登入邮箱
     *
     * @return string
     */
    public function get_email()
    {
        return $this->email;
    }

    /**
     * 获取用户所在宿舍，如果未设置返回 null
     *
     * @return Room|null
     */
    public function get_room()
    {
        return $this->room;
    }

    /**
     * 设置用户所在宿舍
     *
     * @param Room $room
     */
    public function set_room($room)
    {
        global $db;
        $this->room = $room;
        $result = query($db, "
            UPDATE `users` SET `room_id`=%s
            WHERE `id`=%s
            ", $room->get_id(), $this->user_id);
        $db->commit();
    }

    /**
     * 获取用户点数
     *
     * @return int
     */
    public function get_points()
    {
        return $this->points;
    }

    /**
     * 判断密码是否正确
     *
     * @return bool 正确返回 true，否则返回 false
     */
    public function check_password($password)
    {
        $password = crypt($password, $this->password);
        return $password == $this->password;
    }

    /**
     * 生成指定位长的盐值
     *
     * 由于 crypt 大多数算法仅支持使用 ./0-9A-Za-z，
     * 此处生成的也为仅包含这些字符的串。
     *
     * @param int $length
     * @return string
     */
    private static function generate_salt($length)
    {
        static $alphabet =
            './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $ret = '';
        for ($i = 0; $i < $length; ++$i)
            $ret .= $alphabet[mt_rand(0, 63)];
        return $ret;
    }

    /**
     * 设置密码
     *
     * @param string $password
     */
    public function set_password($password)
    {
        global $db;
        $salt = '$1$'.self::generate_salt(8).'$';
        $password = crypt($password, $salt);
        $result = query($db, "
            UPDATE `users` SET `password`='%s'
            WHERE `id`=%s
            ", $password, $this->user_id);
        $this->password = $password;
        $db->commit();
    }

    /**
     * 获得用户的资料
     *
     * @return array
     */
    public function get_info()
    {
        global $db;
        $result = query($db, "
            SELECT `id`, `type`, `address` FROM `userinfo`
            WHERE `user_id`=%s
            ", $this->user_id);
        $ret = array();
        while ($row = $result->fetch_row())
            $ret[$row[0]] = $row[1];
        $result->free();
        return $ret;
    }

    /**
     * 设置用户资料
     *
     * @param string $type 资料类型
     * @param string $address 对应地址
     */
    public function set_info($type, $address)
    {
        global $db;
        $result = query($db, "
            REPLACE INTO `userinfo`
            (`user_id`, `type`, `address`)
            VALUES (%s, '%s', '%s')
            ");
        $db->commit();
    }

    /**
     * 获取用户设置的提醒
     *
     * @return array
     */
    public function get_alerts()
    {
        global $db;
        $result = query($db, "
            SELECT `id`, `type`, `left_days`
            FROM `alerts`
            WHERE `user_id`=%s
            ", $this->user_id);
        $ret = array();
        while ($row = $result->fetch_assoc())
            $ret[] = $row;
        $result->free();
        return $ret;
    }

    /**
     * 添加提醒
     *
     * @param string $type
     * @param int $left_days
     */
    public function add_alert($type, $left_days)
    {
        global $db;
        $result = query($db, "
            INSERT INTO `alerts`
            (`type`, `left_days`)
            VALUES ('%s', %s)
            ", $type, $left_days);
        $db->commit();
    }

    /**
     * 删除提醒
     *
     * @param int $id 提醒编号
     */
    public function remove_alert($id)
    {
        global $db;
        $result = query($db, "
            DELETE FROM `alerts`
            WHERE `id`=%s AND `user_id`=%s
            ", $id, $this->user_id);
    }
}

?>
