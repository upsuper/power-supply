<?php

class Suggest_View extends _View
{
    protected $current = 'suggest';

    function main()
    {
?>
<form action="<?php echo abs_url('/suggest/submit/'); ?>" method="POST" id="suggest">
<p class="label"><label for="content">建议内容</label></p>
<p><textarea name="content"></textarea></p>
<p><label for="email">反馈邮箱</label><input type="email" name="email" id="email" /> (可选)</p>
<p class="note">如果您填写了此项，我们会将答复信息通过这个邮箱发给您。</p>
<p class="act"><input type="submit" value="提交" /></p>
</form>
<?php
    }
}

?>
