<?php

class Query_Controller extends Controller
{
    const PERPAGE = 10;

    function __construct()
    {
        $dc = new DataCenter;
        $this->buildings = $dc->get_building_list();
    }

    function index()
    {
        $district = get_form('district');
        $building = get_form('building');
        $room = get_form('room');
        if ($district && $building && ! $room) {
            flash('请输入房间号', 'warn');
            return redirect();
        } elseif (! $district && isset($_SESSION['user'])) {
            $r = $_SESSION['user']->get_room();
            if ($r) {
                $district = $r->get_district();
                $building = $r->get_building();
                $room = $r->get_room();
            }
        }
        if ($district && $building && $room) {
            $url = implode('/', array(
                '', 'query',
                rawurlencode($district),
                rawurlencode($building),
                rawurlencode($room),
                ''));
            return redirect($url);
        }

        $data = array('buildings' => $this->buildings);
        if (isset($_SESSION['query_form'])) {
            $data['form'] = $_SESSION['query_form'];
            unset($_SESSION['query_form']);
        }
        return template('Query', $data);
    }

    /**
     * 尝试获取房间数据
     *
     * @param string $district
     * @param string $building
     * @param string $room
     * @return Room|false 成功返回房间，失败返回 false
     */
    static function try_get_room_data($district, $building, $room)
    {
        $r = new Room($district, $building, $room);
        // 如果不在数据库中则尝试从能源管理中心抓取数据
        if (! $r->get_id()) {
            $dc = new DataCenter;
            $data = $dc->fetch_data($district, $building, $room);
            if (! $data)
                return false;
            $r->update_records($data);
            global $CONFIG;
            $f = new $CONFIG['forecaster'];
            $f->update($r);
        }
        return $r;
    }

    function __call($district, $args)
    {
        global $CONFIG;

        $building = array_shift($args);
        $room = array_shift($args);
        $page = intval(array_shift($args));
        if ($page < 1) $page = 1;

        $_SESSION['query_form'] = array(
            'district' => $district,
            'building' => $building,
            'room' => $room
        );

        $r = self::try_get_room_data($district, $building, $room);
        if (! $r) {
            flash('宿舍不存在或数据获取失败', 'fail');
            redirect('/query/');
        }

        $data = array();
        $records =
            $r->get_records(self::PERPAGE + 1, ($page - 1) * self::PERPAGE);
        $output = array();
        for ($i = 0; $i < count($records) - 1; ++$i) {
            $record = $records[$i];
            $before = $records[$i + 1];
            list($ave, $days) = Room::diff_items($record, $before);
            $output[] = array(
                'date' => $record['date'],
                'used' => sprintf('%.2f', $ave),
                'left' => sprintf('%.2f', $record['left'])
            );
        }
        $data['records'] = $output;

        $last = $records[0];
        $f = new $CONFIG['forecaster'];
        $left_days = $f->forecast($r, $last['left'], $last['date']);
        if (is_finite($left_days)) {
            $now = new DateTime;
            $day_diff = $now->diff(new DateTime($last['date']))->d;
            $left_days = intval(ceil($left_days) - $day_diff);
            if ($left_days < 0) $left_days = 0;
        }
        $data['left_days'] = $left_days;

        $data['buildings'] = $this->buildings;
        $data['form'] = $_SESSION['query_form'];
        return template('Query', $data);
    }
}

?>
