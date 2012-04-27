<?php

class UserIndex_View extends User_View
{
    function title()
    {
        parent::title();
        echo '用户信息';
    }

    function main()
    {
?>
<form action="" method="POST" id="userinfo">
<ul>
    <li>
        <label for="email">邮箱</label>
        <input type="email" name="email" id="email" value="<?php echo htmlentities($this->email); ?>" />
    </li>
    <li>
    	<label for="district">校区</label>
        <select id="district" name="district">
        <?php foreach ($this->buildings as $district => $buildings): ?>
            <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
        <?php endforeach; ?>
        </select>
    </li>
    <li>
    	<label for="building">楼号</label>
    	<select id="building" name="building"></select>
    </li>
    <li>
    	<label for="room">房号</label>
    	<input type="text" name="room" id="room" />
    </li>
    <li>
    	<label for="sms">手机</label>
        <input type="text" name="sms" id="sms" value="<?php if (isset($this->info['sms'])) echo $this->info['sms']; ?>" />
    </li>
    <li>
    	<label for="renren">人人网</label>
        <?php if (isset($this->info['renren'])): ?>
        <!-- TODO -->
        <?php else: ?>
        <a href="#" id="connect_to_renren">与人人连接</a>
        <?php endif; ?>
    </li>
</ul>
<p class="act">
    <input type="submit" value="修改" />
</p>
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
        for (var i = 0; i < bs.length; ++i)
            $building.append($('<option>').val(bs[i]).text(bs[i]));
    });
    $('#district').val(<?php echo json_encode($this->room['district']); ?>);
    $('#district').change();
    $('#building').val(<?php echo json_encode($this->room['building']); ?>);
    $('#room').val(<?php echo json_encode($this->room['room']); ?>);
</script>
</form>
<form action="<?php echo abs_url('/user/alert/'); ?>" method="POST" id="alert">
<h2>提醒方式设置</h2>
<table>
    <thead>
        <tr>
            <th>提前</th>
            <th class="email">邮件</th>
            <th class="sms">手机短信</th>
            <th class="renren">人人提示</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>7天</th>
            <td><input type="checkbox" name="email_7" /></td>
            <td><input type="checkbox" name="sms_7" /></td>
            <td><input type="checkbox" name="renren_7" /></td>
        </tr>
        <tr>
            <th>3天</th>
            <td><input type="checkbox" name="email_3" /></td>
            <td><input type="checkbox" name="sms_3" /></td>
            <td><input type="checkbox" name="renren_3" /></td>
        </tr>
        <tr>
            <th>1天</th>
            <td><input type="checkbox" name="email_1" /></td>
            <td><input type="checkbox" name="sms_1" /></td>
            <td><input type="checkbox" name="renren_1" /></td>
        </tr>
    </tbody>
</table>
<p class="act">
    <input type="submit" value="修改" />
</p>
</form>
<?php
    }
}

?>
