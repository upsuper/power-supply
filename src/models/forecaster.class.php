<?php

interface Forecaster
{
    /**
     * 预测一个房间给定剩余电量在指定日期开始（含）还可以使用几天
     *
     * @param Room $room 房间
     * @param float $left 剩余电量
     * @param string $date 可选，开始计算的日期，默认为当天
     * @return float 预测可用天数，如果平均用电量为0，返回 INF
     */
    public function forecast($room, $left, $date = null);

    /**
     * 更新房间的预测信息
     *
     * @param Room $room
     */
    public function update($room);
}

?>
