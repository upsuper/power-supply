<?php

class DataCenter
{
    const DATASOURCE_URL =
        'http://nyglzx.tongji.edu.cn/web/datastat.aspx';
    private $stmt = null;
    private $queried = array();

    public function __construct()
    {
        global $db;
        $this->stmt = $db->prepare('
            SELECT `viewstate`, `validation`
            FROM `center_data`
            WHERE `district`=?
            ');
    }

    public function __destruct()
    {
        $this->stmt->close();
    }

    /**
     * 转换到浮点数
     *
     * 剔除不必要的字符，并将字符串转换为浮点数
     *
     * @param string $input 
     * @return float
     */
    private static function to_float($input)
    {
        return floatval(preg_replace('/[^-0-9\.]/', '', $input));
    }

    /**
     * 获取当前用电数据
     *
     * @param string $district 校区名
     * @param string $building 楼号
     * @param string $room 房号
     * @return array 用电数据
     */
    public function fetch_data($district, $building, $room)
    {
        if (isset($this->queried[$district])) {
            list($viewstate, $validation) = $this->queried[$district];
        } else {
            $stmt = $this->stmt;
            $stmt->bind_param('s', $district);
            $stmt->execute();
            $stmt->bind_result($viewstate, $validation);
            $stmt->fetch();
            $stmt->reset();
            $this->queried[$district] = array($viewstate, $validation);
        }

        $fields = http_build_query(array(
            '__EVENTTARGET' => '',
            '__EVENTARGUMENT' => '',
            '__LASTFOCUS' => '',
            '__VIEWSTATE' => $viewstate,
            '__EVENTVALIDATION' => $validation,
            'DistrictDown' => $district,
            'BuildingDown' => $building,
            'RoomnameText' => $room,
            'Submit' => '查询'
        ));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::DATASOURCE_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);

        $pattern =
            '|<td><font color="Black">(\d{4}-\d{2}-\d{2})</font></td>'.
            '<td><font color="Black">([\d,\.]+)</font></td>'.
            '<td><font color="Black">([\d,\.]+)</font></td>'.
            '<td><font color="Black">(-?[\d,\.]+)</font></td>|';
        preg_match_all($pattern, $data, $matches, PREG_SET_ORDER);

        $data = array();
        foreach ($matches as $item) {
            $data[] = array(
                'date' => $item[1],
                'used' => self::to_float($item[2]),
                'purchased' => self::to_float($item[3]),
                'left' => self::to_float($item[4]),
            );
        }

        return $data;
    }

    /**
     * 获取所有校区所有楼名的数据
     *
     * 返回数据每项的键为校区名，值为校区对应的楼名列表的
     * JSON 格式数据。
     *
     * @return array
     */
    public function get_building_list()
    {
        global $db;
        $result = query($db, "
            SELECT `district`, `buildings`
            FROM `center_data`
            ");
        $ret = array();
        while ($row = $result->fetch_row())
            $ret[$row[0]] = $row[1];
        $result->free();
        return $ret;
    }
}

?>
