<?php

class UserLogin_View extends User_View
{
    function title()
    {
        parent::title();
        echo ' - 登入';
    }

    function main()
    {
?>
<form action="" method="POST" id="login">
    <p><label for="email">邮箱</label><input type="email" name="email" id="email" /></p>
    <p><label for="pass">密码</label><input type="password" name="password" id="pass" /></p>
    <p class="act"><input type="submit" value="登入" /> <a href="<?php echo abs_url('/user/register/'); ?>" class="button">注册</a></p>
</form>
<?php
    }
}

?>
