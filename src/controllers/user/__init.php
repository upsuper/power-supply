<?php

class User_Controller extends Controller
{
    public function __construct($path)
    {
        parent::__construct();
        $next = $path[0];
        if (isset($_SESSION['user'])) {
            if ($next == 'login' || $next == 'register')
                throw redirect('/user/');
        } else {
            if ($next != 'login' && $next != 'register')
                throw redirect('/user/login/');
        }
    }

    /**
     * 设置登入
     */
    private function set_login($user)
    {
        $_SESSION['user'] = $user;
    }

    public function index()
    {
        $data = array();

        $user = $_SESSION['user'];
        $data['email'] = $user->get_email();
        $data['info'] = $user->get_info();
        $room = $user->get_room();
        if ($room) {
            $data['room'] = array(
                'district' => $room->get_district(),
                'building' => $room->get_building(),
                'room' => $room->get_room()
            );
        }

        $dc = new DataCenter;
        $data['buildings'] = $dc->get_building_list();
        return template('UserIndex', $data);
    }

    public function login()
    {
        $email = get_form('email');
        $password = get_form('password');
        if (! $email || ! $password)
            return template('UserLogin');

        $user = User::get_user($email);
        if (! $user) {
            flash('用户不存在', 'fail');
            return redirect();
        } elseif (! $user->check_password($password)) {
            flash('密码错误', 'fail');
            return redirect();
        }

        $this->set_login($user);
        return redirect('/user/');
    }

    public function logout()
    {
        unset($_SESSION['user']);
        return redirect('/query/');
    }

    public function register()
    {
        $email = get_form('email');
        $password = get_form('password');
        if (! $email || ! $password) {
            $dc = new DataCenter;
            $data['buildings'] = $dc->get_building_list();
            return template('UserRegister', $data);
        }

        $user = User::get_user($email);
        if ($user) {
            flash('用户已存在，请直接登入', 'fail');
            return redirect('/user/login/');
        }

        $user = User::add_user($email);
        if (! $user) {
            flash('创建时发生错误', 'error');
            return redirect();
        }
        $user->set_password($password);
        $this->set_login($user);
        flash('注册成功', 'success');

        $district = get_form('district');
        $building = get_form('building');
        $room = get_form('room');
        if ($district && $building && $room) {
            // FIXME 应该使用 autoload 而不是手工载入
            include(CTRL_PATH.'/query.php');
            $r = Query_Controller::try_get_room_data(
                $district, $building, $room);
            if ($r)
                $user->set_room($r);
            else
                flash('所选择的宿舍不存在或数据获取失败', 'fail');
        }

        redirect('/user/');
    }
}

?>
