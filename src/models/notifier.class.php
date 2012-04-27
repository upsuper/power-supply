<?php

interface Notifier
{
    /**
     * 发送电量耗倾的提醒
     *
     * @param string $address 要发送的目标地址
     * @param Room $room 房间
     * @param int $leftdays 耗倾天数
     * @return bool 是否发送成功
     */
    public function notify($address, $room, $leftdays);
}

?>
