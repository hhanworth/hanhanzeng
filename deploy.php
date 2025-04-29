<?php
/**
 * update.php
 * 
 * 1. 从 GitHub 下载指定仓库分支的 ZIP 包
 * 2. 递归删除当前目录下除本脚本外的所有文件/文件夹
 * 3. 解压 ZIP 包到临时目录
 * 4. 将解压出来的内容复制回当前目录
 * 5. 清理临时文件
 * 
 * 使用前请修改下面的配置项：$repoOwner、$repoName、$branch
 */

// —— 配置区 —— //
$repoOwner = 'hhanworth';
$repoName  = 'hanhanzeng';
$branch    = 'main';  // 或你想要的分支，如 master

$zipUrl     = "https://github.com/{$repoOwner}/{$repoName}/archive/refs/heads/{$branch}.zip";
$tempDir    = sys_get_temp_dir();
$tempZip    = "{$tempDir}/{$repoName}.zip";
$extractDir = "{$tempDir}/{$repoName}-{$branch}";

// —— 工具函数 —— //
/**
 * 递归删除文件或目录
 */
function rrmdir($path) {
    if (!file_exists($path)) return;
    if (is_dir($path) && !is_link($path)) {
        $files = array_diff(scandir($path), ['.','..']);
        foreach ($files as $file) {
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
function rcopy($src, $dst) {
    if (is_dir($src)) {
        @mkdir($dst, 0755, true);
        $files = array_diff(scandir($src), ['.','..']);
        foreach ($files as $file) {
            rcopy("{$src}/{$file}", "{$dst}/{$file}");
        }
    } else {
        copy($src, $dst);
    }
}

// —— 主流程 —— //
try {
    // 1. 下载 ZIP
    $fp = fopen($tempZip, 'w');
    $ch = curl_init($zipUrl);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    if (!curl_exec($ch)) {
        throw new Exception('下载失败：'. curl_error($ch));
    }
    curl_close($ch);
    fclose($fp);

    // 2. 删除当前目录下除自身脚本之外的所有文件/文件夹
    $root = __DIR__;
    $self = basename(__FILE__);
    foreach (scandir($root) as $item) {
        if (in_array($item, ['.','..', $self])) continue;
        rrmdir("{$root}/{$item}");
    }

    // 3. 解压 ZIP 到临时目录
    $zip = new ZipArchive;
    if ($zip->open($tempZip) !== true) {
        throw new Exception('无法打开 ZIP 文件');
    }
    // 先清理旧目录
    rrmdir($extractDir);
    mkdir($extractDir, 0755, true);
    $zip->extractTo($extractDir);
    $zip->close();

    // 4. 将解压后的内容复制回根目录
    // GitHub ZIP 解压后目录名通常是 “repoName-branch”
    $extractedRoot = "{$extractDir}/{$repoName}-{$branch}";
    if (!is_dir($extractedRoot)) {
        throw new Exception('解压目录不存在：'. $extractedRoot);
    }
    rcopy($extractedRoot, $root);

    // 5. 清理临时文件
    unlink($tempZip);
    rrmdir($extractDir);

    echo "✅ 更新完成！请刷新页面查看最新内容。";
} catch (Exception $e) {
    http_response_code(500);
    echo "❌ 更新失败：", $e->getMessage();
}
