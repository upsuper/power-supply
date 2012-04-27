<?php

class UserRegister_View extends User_View
{
    function title()
    {
        parent::title();
        echo ' - 注册';
    }

    function main()
    {
?>
<form action="" method="POST" id="register">
<p><label for="email">邮箱</label><input type="email" name="email" id="email" required="required" /></p>
<p><label for="pass">密码</label><input type="password" name="password" id="pass" required="required" /></p>
<p><label for="verifypass">确认密码</label><input type="password" id="verifypass" required="required" /></p>
<h2>订阅宿舍</h2>
<p><label for="district">校区</label><select id="district" name="district">
    <option value="">请选择校区</option>
<?php foreach ($this->buildings as $district => $buildings): ?>
    <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
<?php endforeach; ?>
</select></p>
<p><label for="building">楼号</label><select id="building" name="building"></select></p>
<p><label for="room">房号</label><input type="text" name="room" id="room" /></p>
<p class="act"><input type="submit" value="注册" /></p>
</form>
<script type="text/javascript">
    var buildings = {
    <?php foreach ($this->buildings as $district => $buildings): ?>
        <?php echo json_encode($district); ?>: <?php echo $buildings; ?>,
    <?php endforeach; ?>
        '': []
    };
    $('#district').change(function() {
        var $building = $('#building');
        var bs = buildings[$(this).val()];
        $building.empty();
        $building.append($('<option>').val('').text('请选择楼号'));
        for (var i = 0; i < bs.length; ++i)
            $building.append($('<option>').val(bs[i]).text(bs[i]));
    });
    $('#register').submit(function(e) {
        if ($('#pass').val() != $('#verifypass').val()) {
            alert('两次输入的密码不相同');
            e.preventDefault();
        }
    });
    <?php if ($_GET['district']): ?>
    $('#district').val(<?php echo json_encode($_GET['district']); ?>);
    $('#district').change();
    <?php endif; ?>
    <?php if ($_GET['building']): ?>
    $('#building').val(<?php echo json_encode($_GET['building']); ?>);
    <?php endif; ?>
    <?php if ($_GET['room']): ?>
    $('#room').val(<?php echo json_encode($_GET['room']); ?>);
    <?php endif; ?>
</script>
<?php
    }
}

?>
