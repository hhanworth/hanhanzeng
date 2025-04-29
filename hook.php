<?php
/*
 * @Author: hhan
 * @Date: 2025-04-29 16:21:54
 * @LastEditTime: 2025-04-29 16:21:59
 * @Description: 
 */
/**
 * hook.php
 * 1. 验证 GitHub 发送过来的 HMAC-SHA256 签名，防止伪造
 * 2. 只处理 push 事件
 * 3. 调用 update.php 脚本完成项目更新
 */

// —— 配置区 —— //
$secret = 'sdfghjkl'; // GitHub Webhook Secret，确保与 GitHub 设置一致

// —— 验签 —— //
$payload = file_get_contents('php://input');
if (!$payload) {
    http_response_code(400);
    exit('No payload');
}
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
if (!$signature) {
    http_response_code(403);
    exit('Missing signature');
}

$hash = 'sha256=' . hash_hmac('sha256', $payload, $secret, false);
if (!hash_equals($hash, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

// —— 只处理 push 事件 —— //
$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event !== 'push') {
    http_response_code(200);
    exit('Event ignored: ' . $event);
}

// （可选）检查是否是你关心的分支
$data = json_decode($payload, true);
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/main') {
    http_response_code(200);
    exit('Not the main branch: ' . $ref);
}

// —— 调用更新脚本 —— //
try {
    // 如果 update.php 返回错误码或异常，会在网页上显示
    require __DIR__ . '/update.php';
    // update.php 里 echo “✅ 更新完成”，这里我们也可以返回 200
    http_response_code(200);
    echo "Hook handled successfully";
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Update failed: ' . $e->getMessage());
    echo "Update failed: " . $e->getMessage();
}
