<?php
/**
 * hook.php
 *
 * 1. 验证 GitHub 发送过来的 HMAC-SHA256 签名，防止伪造
 * 2. 只处理 push 事件和指定分支
 * 3. 从 GitHub 下载 ZIP 包并自动更新项目
 *
 * 更新完成后，会输出“✅ 更新完成”或在出错时输出相应错误提示。
 */

// —— 配置区 —— //
$secret    = 'sdfghjkl';               // GitHub Webhook Secret
$repoOwner = 'hhanworth';              // 仓库所有者
$repoName  = 'hanhanzeng';             // 仓库名称
$branch    = 'main';                   // 分支名称，如 main 或 master

$zipUrl     = "https://github.com/{$repoOwner}/{$repoName}/archive/refs/heads/{$branch}.zip";
$tempDir    = sys_get_temp_dir();
$tempZip    = "{$tempDir}/{$repoName}.zip";
$extractDir = "{$tempDir}/{$repoName}-{$branch}";

// —— 验签 —— //
$payload   = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (empty($payload)) {
    http_response_code(400);
    exit('No payload');
}
if (empty($signature)) {
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
    exit("Event ignored: {$event}");
}

// —— 只处理指定分支 —— //
$data = json_decode($payload, true);
$ref  = $data['ref'] ?? '';
if ($ref !== "refs/heads/{$branch}") {
    http_response_code(200);
    exit("Not the {$branch} branch: {$ref}");
}

// —— 工具函数 —— //
/**
 * 递归删除文件或目录
 */
function rrmdir(string $path): void {
    if (!file_exists($path)) return;
    if (is_dir($path) && !is_link($path)) {
        foreach (array_diff(scandir($path), ['.','..']) as $file) {
            rrmdir($path . DIRECTORY_SEPARATOR . $file);
        }
        rmdir($path);
    } else {
        unlink($path);
    }
}

/**
 * 递归复制文件或目录
 */
function rcopy(string $src, string $dst): void {
    if (is_dir($src)) {
        @mkdir($dst, 0755, true);
        foreach (array_diff(scandir($src), ['.','..']) as $file) {
            rcopy("{$src}/{$file}", "{$dst}/{$file}");
        }
    } else {
        copy($src, $dst);
    }
}

// —— 更新流程 —— //
try {
    // 1. 下载 ZIP
    $fp = fopen($tempZip, 'w');
    if (!$fp) {
        throw new Exception("无法写入临时文件：{$tempZip}");
    }
    $ch = curl_init($zipUrl);
    curl_setopt_array($ch, [
        CURLOPT_FILE           => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FAILONERROR    => true,
    ]);
    if (!curl_exec($ch)) {
        throw new Exception('下载失败：'. curl_error($ch));
    }
    curl_close($ch);
    fclose($fp);

    // 2. 删除当前目录下除钩子脚本外的所有文件/文件夹
    $root = __DIR__;
    $self = basename(__FILE__); // 通常是 hook.php
    foreach (scandir($root) as $item) {
        if (in_array($item, ['.','..', $self], true)) {
            continue;
        }
        rrmdir("{$root}/{$item}");
    }

    // 3. 解压 ZIP 到临时目录
    $zip = new ZipArchive;
    if ($zip->open($tempZip) !== true) {
        throw new Exception('无法打开 ZIP 文件');
    }
    // 清理旧解压目录
    rrmdir($extractDir);
    mkdir($extractDir, 0755, true);
    $zip->extractTo($extractDir);
    $zip->close();

    // 4. 将解压后的内容复制回根目录
    $extractedRoot = "{$extractDir}/{$repoName}-{$branch}";
    if (!is_dir($extractedRoot)) {
        throw new Exception("解压目录不存在：{$extractedRoot}");
    }
    rcopy($extractedRoot, $root);

    // 5. 清理临时文件
    unlink($tempZip);
    rrmdir($extractDir);

    // 成功响应
    http_response_code(200);
    echo "✅ 更新完成！";
} catch (Throwable $e) {
    // 失败响应
    http_response_code(500);
    error_log('Update failed: ' . $e->getMessage());
    echo "❌ 更新失败：", $e->getMessage();
}
