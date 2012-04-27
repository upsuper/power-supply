<?php

abstract class _View extends View
{
    protected $current;

    function title()
    {
        echo '宿舍供电管理系统';
    }

    function head()
    {
?>
<meta charset="UTF-8">
<title><?php $this->title(); ?></title>
<link rel="stylesheet" href="<?php echo STATIC_URI.'/style/reset.css'; ?>" />
<link rel="stylesheet" href="<?php echo STATIC_URI.'/style/style.css'; ?>" />
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<?php
    }

    function flash()
    {
?>
<?php if (isset($_SESSION['flash']) && $_SESSION['flash']): ?>
<?php while ($_SESSION['flash']): ?>
    <?php $msg = array_shift($_SESSION['flash']); ?>
    <div class="flash <?php echo $msg[1]; ?>"><?php echo $msg[0]; ?></div>
<?php endwhile; ?>
<script type="text/javascript">
    $('.flash').click(function(e) {
        var $t = $(this);
        $t.slideUp('fast', function() { $t.remove(); });
    });
</script>
<?php endif; ?>
<?php
    }

    function nav()
    {
?>
<nav id="nav_act">
    <a href="<?php echo abs_url('/query/'); ?>"<?php if ($this->current == 'query'): ?> class="select"<?php endif; ?>>用电查询</a>
    <a href="<?php echo abs_url('/user/'); ?>"<?php if ($this->current == 'user'): ?> class="select"<?php endif; ?>>用户信息</a>
    <a href="<?php echo abs_url('/suggest/'); ?>"<?php if ($this->current == 'suggest'): ?> class="select"<?php endif; ?>>投诉建议</a>
</nav>
<nav id="nav_user">
<?php if (isset($_SESSION['user'])): ?>
    <a href="<?php echo abs_url('/user/'); ?>"><?php echo $_SESSION['user']->get_email(); ?></a>
    <a href="<?php echo abs_url('/user/logout/'.get_token()); ?>">注销</a>
<?php else: ?>
    <a href="<?php echo abs_url('/user/login/'); ?>">登入</a>
    <a href="<?php echo abs_url('/user/register/'); ?>">注册</a>
<?php endif; ?>
</nav>
<?php
    }

    function footer()
    {
?>
<div id="footer">
&copy; 2011, 2012 软件工程第三小组. Some rights reserved.
</div>
<?php
    }

    function body()
    {
?>
<div id="page">
    <h1>宿舍供电管理系统</h1>
    <?php $this->nav(); ?>
    <div id="main">
        <?php $this->flash(); ?>
        <?php $this->main(); ?>
    </div>
    <?php $this->footer(); ?>
</div>
<?php
    }

    function page()
    {
?>
<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<?php $this->head(); ?>
</head>
<body>
<?php $this->body(); ?>
</body>
</html>
<?php
    }
}

?>
