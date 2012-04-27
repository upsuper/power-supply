<?php

class Forecaster_Gradual implements Forecaster
{
    /**
     * 学习参数
     */
    const ALPHA = 0.1;

    private $query_stmt;
    private $update_stmt;

    public function __construct()
    {
        global $db;
        $this->query_stmt = $db->prepare("
            SELECT `average`, `last_date`
            FROM `forecaster_gradual`
            WHERE `room_id`=?
            ");
        $this->update_stmt = $db->prepare("
            UPDATE `forecaster_gradual`
            SET `average`=?, `last_date`=?
            WHERE `room_id`=?
            ");
    }

    /**
     * 获得指定房间在预测器中的数据
     *
     * @param Room $room
     * @return array 两项，分别为之前的平均数和最后计算的日期
     */
    private function get_room_data($room)
    {
        $room_id = $room->get_id();
        $stmt = $this->query_stmt;
        $stmt->bind_param('i', $room_id);
        $stmt->execute();
        $stmt->bind_result($average, $last_date);
        $stmt->fetch();
        $stmt->reset();
        return array(floatval($average), $last_date);
    }

    public function forecast($room, $left, $date = null)
    {
        list($average, $last_date) = $this->get_room_data($room);
        if (! $average)
            return INF;
        return $left / $average;
    }

    /**
     * 根据房间用电数据计算新的平均用电量
     *
     * @param array $data 用电数据
     * @param float $start 之前的平均值
     * @return float
     */
    private static function comput_average($data, $start = 0.0)
    {
        if (count($data) < 2)
            return $start;
        if ($start) {
            $ret = $start;
            for ($i = 1; $i < count($data); ++$i) {
                list($ave, $days) = Room::diff_items($data[$i], $data[$i - 1]);
                for ($j = 0; $j < $days; ++$j)
                    $ret = (1 - self::ALPHA) * $ret + self::ALPHA * $ave;
            }
        } else {
            $first = reset($data);
            $last = end($data);
            list($ave, $days) = Room::diff_items($last, $first);
            $ret = $ave;
        }
        return $ret;
    }

    public function update($room)
    {
        global $db;
        list($average, $last_date) = $this->get_room_data($room);
        $room_id = $room->get_id();
        if (! $average) {
            $data = $room->query_records();
            $average = self::comput_average($data);
            $last_item = end($data);
            $last_date = $last_item['date'];
            $stmt = $db->prepare("
                INSERT INTO `forecaster_gradual`
                (`room_id`, `average`, `last_date`)
                VALUES (?, ?, ?)
                ");
            $stmt->bind_param('ids', $room_id, $average, $last_date);
            $stmt->execute();
            $stmt->reset();
        } else {
            $data = $room->query_records($last_date);
            $average = $this->comput_average($data, $average);
            $last_item = end($data);
            $last_date = $last_item['date'];
            $stmt = $this->update_stmt;
            $stmt->bind_param('dsi', $average, $last_date, $room_id);
            $stmt->execute();
            $stmt->reset();
        }
        $db->commit();
    }
}

?>
