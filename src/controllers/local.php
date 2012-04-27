<?php

class Local_Controller extends Controller
{
    public function __construct()
    {
        header('Content-Type: text/plain; charset=UTF-8');
        ignore_user_abort(true);
        set_time_limit(0);
    }

    public function refresh()
    {
        $dc = new DataCenter();
        $fc = new Forecaster_Gradual();
        $rooms = Room::get_all_rooms();
        foreach ($rooms as $r) {
            $district = $r->get_district();
            $building = $r->get_building();
            $room = $r->get_room();
            echo "$district, $building, $room\n";
            $data = $dc->fetch_data($district, $building, $room);
            $r->update_records($data);
            $fc->update($r);
        }
    }
}

?>
