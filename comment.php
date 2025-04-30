<?php
/*
 * @Author: hhan
 * @Date: 2025-04-30 13:47:04
 * @LastEditTime: 2025-04-30 13:53:53
 * @Description: 将留言追加到 comments.txt，生产环境请用数据库并做好防注入、验证码校验
 */
// comment.php
// 

// 1. 接收并简单验证
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$url     = trim($_POST['url'] ?? '');
$message = trim($_POST['message'] ?? '');
$captcha = trim($_POST['captcha'] ?? '');

if (!$name || !$email || !$message) {
    die('缺少必填项。');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('无效的 Email。');
}
if ($captcha !== '7') {
    die('人类验证失败，请输入数字 7');
}

// 2. 准备存储
$entry = [
    'time'    => date('Y-m-d H:i:s'),
    'name'    => htmlspecialchars($name, ENT_QUOTES),
    'email'   => htmlspecialchars($email, ENT_QUOTES),
    'url'     => htmlspecialchars($url, ENT_QUOTES),
    'message' => htmlspecialchars($message, ENT_QUOTES),
];
// 存为一行 JSON
$line = json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;

// 3. 追加到文件
file_put_contents(__DIR__ . '/comments.txt', $line, FILE_APPEND | LOCK_EX);

// 4. 跳回首页
header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.html'));
exit;
?>
