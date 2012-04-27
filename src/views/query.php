<?php

class Query_View extends _View
{
    protected $current = 'query';

    function main()
    {
?>
<form action="<?php echo abs_url('/query/'); ?>" method="POST" id="query">
    <p><label for="district">校区</label><select name="district" id="district">
    <?php foreach ($this->buildings as $district => $buildings): ?>
        <option value="<?php echo $district; ?>"><?php echo $district; ?></option>
    <?php endforeach; ?>
    </select></p>
    <p><label for="building">楼号</label><select name="building" id="building"></select></p>
    <p><label for="room">房号</label><input type="text" name="room" id="room" /></p>
    <p class="act"><input type="submit" value="查询" /></p>
    <?php if ($this->records && ! isset($_SESSION['user'])): ?>
    <p class="subscribe">
        <a href="<?php echo abs_url('/user/register/?'.http_build_query(array(
            'district' => $this->form['district'],
            'building' => $this->form['building'],
            'room' => $this->form['room']))); ?>">订阅此宿舍的用电提醒 »</a>
    </p>
    <?php endif; ?>
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
        for (var i = 0; i < bs.length; ++i)
            $building.append($('<option>').val(bs[i]).text(bs[i]));
    });
    <?php if ($this->form): ?>
    $('#district').val(<?php echo json_encode($this->form['district']); ?>);
    <?php endif; ?>
    $('#district').change();
    <?php if ($this->form): ?>
    $('#building').val(<?php echo json_encode($this->form['building']); ?>);
    $('#room').val(<?php echo json_encode($this->form['room']); ?>);
    <?php endif; ?>
</script>
<?php if ($this->records): ?>
<?php if (is_infinite($this->left_days)): ?>
<p class="notice">哇！竟然不用电哦，好神奇的寝室啊~</p>
<?php elseif ($this->left_days > 7): ?>
<p class="notice">预计剩余电量可能于<?php echo $this->left_days; ?>天内耗尽。</p>
<?php elseif ($this->left_days > 0): ?>
<p class="notice warn">预计剩余电量预计将于<?php echo $this->left_days; ?>天内耗尽。</p>
<?php else: ?>
<p class="notice warn">预计剩余电量将在不到1天内耗尽。</p>
<?php endif; ?>
<table id="records">
    <caption>
        <?php echo $this->form['district']; ?>
        <?php echo $this->form['building']; ?>
        <?php echo $this->form['room']; ?>
        用电记录
    </caption>
    <thead>
        <tr>
            <th>日期</th>
            <th>日均用电量</th>
            <th>剩余电量</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($this->records as $record): ?>
        <tr>
            <td><?php echo $record['date']; ?></td>
            <td><?php echo $record['used']; ?></td>
            <td><?php echo $record['left']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php
    }
}

?>
