<?php

class Suggest_Controller extends Controller
{
    function index()
    {
        return template('Suggest');
    }

    function submit()
    {
        $email = get_form('email');
        $content = get_form('content');
        if ($content) {
            $result = Suggest::create($email, $content);
            if ($result) {
                flash('投诉建议发送成功', 'success');
            } else {
                flash('发送投诉建议时发生错误，请重试', 'error');
            }
        }
        return redirect('/suggest/');
    }
}

?>
