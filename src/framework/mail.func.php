<?php

/**
 * 检验是否为有效的电子邮件地址
 *
 * 根据 http://www.linuxjournal.com/article/9585 编写
 *
 * @param string $email
 * @return bool
 */
function is_valid_email($email)
{
    // 是否存在@
    $at_index = strrpos($email, '@');
    if ($at_index === false)
        return false;
    // 分离信息
    $local = substr($email, 0, $at_index);
    $domain = substr($email, $at_index + 1);
    $local_len = strlen($local);
    $domain_len = strlen($domain);
    // 检查有效性
    if ($local_len < 1 || $local_len > 64)
        return false;
    if ($domain_len < 1 || $domain_len > 255)
        return false;
    if ($local[0] == '.' || $local[$local_len - 1] == '.')
        return false;
    if (strpos($local, '..') !== false)
        return false;
    if (strpos($domain, '..') !== false)
        return false;
    if (! preg_match('/^[A-Za-z0-9.-]+[A-Za-z0-9]$/', $domain))
        return false;
    if (! preg_match('/^"[^"]+"$/', $local)) {
        if (! preg_match('/^[A-Za-z0-9!#$%&\'*+\\/=?^_`{|}~.-]+$/', substr($local, 1, -1)))
            return false;
    }
    if (! (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A')))
        return false;
    return true;
}

/**
 * 发送邮件
 *
 * @param string $from 发件人
 * @param string $to 收件人
 * @param string $subject 邮件主题
 * @param string $message 邮件内容
 * @return bool 是否发送成功
 */
function send_mail($from, $to, $subject, $message, $reply_to = false)
{
    preg_match('/<(.+)>/', $from, $from_data);
    $from_email = $from_data ? $from_data[1] : $from;
    if ($reply_to === false)
        $reply_to = $from_email;
    $headers = <<<EOH
From: $from
Reply-To: $reply_to
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
EOH;
    $subject = '=?UTF-8?B?'.base64_encode($subject).'?=';
    return mail($to, $subject, $message, $headers, '-f '.$from_email);
}

?>
