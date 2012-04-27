<?php

class Room
{
    private $room_id = false;
    private $district;
    private $building;
    private $room;

    private static $stmts_inited = false;
    private static $id_stmt;
    private static $query_stmt;
    private static $get_stmt;
    private static $update_stmt;

    /**
     * 初始化数据库查询
     */
    private static function init_stmts()
    {
        global $db;
        self::$id_stmt = $db->prepare("
            SELECT `id` FROM `rooms`
            WHERE `district`=? AND `building`=? AND `room`=?
            ");
        self::$query_stmt = $db->prepare("
            SELECT `date`, `used`, `purchased`, `left`
            FROM `records`
            WHERE `room_id`=? AND `date`>=? AND `date`< ?
            ORDER BY `date` ASC
            ");
        self::$get_stmt = $db->prepare("
            SELECT `date`, `used`, `purchased`, `left`
            FROM `records` WHERE `room_id`=?
            ORDER BY `date` DESC LIMIT ?, ?
            ");
        self::$update_stmt = $db->prepare("
            INSERT IGNORE INTO `records`
            (`room_id`, `date`, `used`, `purchased`, `left`)
            VALUES (?, ?, ?, ?, ?)
            ");
        self::$stmts_inited = true;
    }

    /**
     * 构造函数
     *
     * @param string $district 校区
     * @param string $building 楼号
     * @param string $room 房号
     */
    public function __construct($district, $building, $room)
    {
        if (! self::$stmts_inited)
            self::init_stmts();
        $this->district = $district;
        $this->building = $building;
        $this->room = $room;
    }

    /**
     * 在数据库中建立相应房间条目
     * 如果已经存在则直接返回房间 ID
     *
     * @return int 返回房间 ID
     */
    public function create()
    {
        global $db;
        if ($this->get_id())
            return $this->room_id;
        $stmt = $db->prepare("
            INSERT INTO `rooms`
            (`district`, `building`, `room`)
            VALUES (?, ?, ?)
            ");
        $stmt->bind_param('sss',
            $this->district, $this->building, $this->room);
        $stmt->execute();
        $room_id = $stmt->insert_id;
        $stmt->reset();
        $db->commit();
        $this->room_id = $room_id;
        return $room_id;
    }

    /**
     * 获得房间 ID
     *
     * @return int
     */
    public function get_id()
    {
        if ($this->room_id === false) {
            $stmt = self::$id_stmt;
            $stmt->bind_param('sss',
                $this->district, $this->building, $this->room);
            $stmt->execute();
            $stmt->bind_result($room_id);
            $stmt->fetch();
            $stmt->reset();
            if ($room_id)
                $this->room_id = $room_id;
        }
        return $this->room_id;
    }

    public function get_district()
    {
        return $this->district;
    }

    public function get_building()
    {
        return $this->building;
    }

    public function get_room()
    {
        return $this->room;
    }

    /**
     * 将查询结果转化为用电记录
     *
     * @param MySQLi_STMT $stmt
     * @return array
     */
    private static function build_data($stmt)
    {
        $stmt->bind_result($date, $used, $purchased, $left);
        $ret = array();
        while ($stmt->fetch()) {
            $ret[] = array(
                'date' => $date,
                'used' => $used,
                'purchased' => $purchased,
                'left' => $left
            );
        }
        return $ret;
    }

    /**
     * 查询用电记录，并按照日期顺序输出
     *
     * @param string $since_date 可选，记录开始日期（含）
     * @param string $end_date 可选，记录结束日期（不含）
     * @return array 用电记录
     */
    public function query_records($since_date = null, $end_date = null)
    {
        $stmt = self::$query_stmt;
        if (! $since_date)
            $since_date = '1000-01-01';
        if (! $end_date)
            $end_date = '9999-12-31';
        $room_id = $this->get_id();
        if (! $room_id)
            return array();
        $stmt->bind_param('iss', $room_id, $since_date, $end_date);
        $stmt->execute();
        $ret = self::build_data($stmt);
        $stmt->reset();
        return $ret;
    }

    /**
     * 获取用电记录，并按日期逆序输出
     *
     * @param int $limit 可选，输出条数，为0则不限制
     * @param int $offset 可选，偏移数
     * @return array 用电记录
     */
    public function get_records($limit = 0, $offset = 0)
    {
        $stmt = self::$get_stmt;
        if (! $limit)
            $limit = 1024;
        $room_id = $this->get_id();
        if (! $room_id)
            return array();
        $stmt->bind_param('iii', $room_id, $offset, $limit);
        $stmt->execute();
        $ret = self::build_data($stmt);
        $stmt->reset();
        return $ret;
    }

    /**
     * 更新房间的记录
     *
     * @param array $data
     */
    public function update_records($data)
    {
        $room_id = $this->create();
        $stmt = self::$update_stmt;
        $stmt->bind_param('isddd', $room_id, $date, $used, $purchased, $left);
        foreach ($data as $item) {
            extract($item);
            $stmt->execute();
        }
        $stmt->reset();
        global $db;
        $db->commit();
    }

    /**
     * 获得所有房间列表
     *
     * @return array 所有房间列表
     */
    public static function get_all_rooms()
    {
        global $db;
        $result = query($db, "
            SELECT `id`, `district`, `building`, `room`
            FROM `rooms`
            ");
        $ret = array();
        while ($row = $result->fetch_row()) {
            $room = new Room($row[1], $row[2], $row[3]);
            $room->room_id = $row[0];
            $ret[] = $room;
        }
        $result->free();
        return $ret;
    }

    /**
     * 以 room_id 来获取房间实例
     *
     * @param int $id
     * @return Room|false
     */
    public static function get_room_by_id($id)
    {
        static $rooms = array();
        global $db;
        if (! isset($rooms[$id])) {
            $row = query_one($db, "
                SELECT `district`, `building`, `room`
                FROM `rooms` WHERE `id`=%s
                ", $id);
            $rooms[$id] = $row;
        } else {
            $row = $rooms[$id];
        }
        $ret = new Room($row['district'], $row['building'], $row['room']);
        $ret->room_id = $id;
        return $ret;
    }

    /**
     * 计算两个条目之间的天数和用电平均值
     *
     * @param array $item1 被减项
     * @param array $item0 减项
     * @return array 返回两项，第一项为平均值，第二项为天数
     */
    public static function diff_items($item1, $item0)
    {
        $used = $item1['used'] - $item0['used'];
        $date1 = new DateTime($item1['date']);
        $date0 = new DateTime($item0['date']);
        $days = $date1->diff($date0)->d;
        $ave = $used / $days;
        return array($ave, $days);
    }
}

?>
