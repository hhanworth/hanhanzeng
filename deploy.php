<?php
/**
 * deploy.php
 * 访问 https://hhan.top/anything.php 时都会执行这里：
 * 1. 从 GitHub 下载指定仓库 ZIP
 * 2. 解压到临时目录
 * 3. 同步文件到网站根：新增/更新/删除
 * 4. 清理临时文件
 */

// —— 配置区 ——
// GitHub 仓库信息
$githubUser = 'hhanworth';
$githubRepo = 'hanhanzeng';
$branch     = 'main';  // 或者 'master'

// 本地目录设置
$rootDir    = __DIR__;                  // 网站根目录
$tempDir    = $rootDir . 'tmp_deploy';// 临时解压目录
$zipPath    = $rootDir . "{$branch}.zip";// 临时 ZIP 存放

// 不要被删除或覆盖的白名单
$whitelist = [
    //'deploy.php',
    // 你自己需要保留的其他文件或目录
];

// —— 开始部署 ——

// 1. 下载 GitHub ZIP
$zipUrl = "https://github.com/{$githubUser}/{$githubRepo}/archive/refs/heads/{$branch}.zip";
if (!downloadFile($zipUrl, $zipPath)) {
    http_response_code(500);
    exit("❌ 下载 ZIP 失败：{$zipUrl}");
}

// 2. 解压到临时目录
if (is_dir($tempDir)) {
    deleteDir($tempDir);
}else{
    echo "无tempDir";
    mkdir($tempDir, 0755, true);

}
mkdir($tempDir, 0755, true);
$zip = new ZipArchive;
if ($zip->open($zipPath) !== TRUE) {
    http_response_code(500);
    exit("❌ 无法打开 ZIP 文件");
}
$zip->extractTo($tempDir);
$zip->close();

// 解压后目录通常是 "{$repo}-{$branch}/"
$extractedRoot = glob($tempDir . "/*", GLOB_ONLYDIR)[0] ?? null;
if (!$extractedRoot || !is_dir($extractedRoot)) {
    http_response_code(500);
    exit("❌ 没有在 ZIP 中找到解压根目录");
}

// 3. 同步：新增/更新
syncDirectories($extractedRoot, $rootDir, $whitelist);

// 4. 同步：删除多余
cleanupExtraFiles($extractedRoot, $rootDir, $whitelist);

// 5. 清理 ZIP 和临时目录
unlink($zipPath);
deleteDir($tempDir);

// 完成
echo "✅ 部署完成！";



/**
 * 下载远程文件到本地
 */
function downloadFile(string $url, string $savePath): bool {
    $fp = fopen($savePath, 'w');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);
    fclose($fp);
    return $err === 0;
}

/**
 * 递归同步源目录到目标目录（新增和更新）
 */
function syncDirectories(string $src, string $dst, array $whitelist) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($it as $item) {
        $relPath = substr($item->getPathname(), strlen($src) + 1);
        $destPath = $dst . '/' . $relPath;

        // 白名单里的文件/目录不处理
        foreach ($whitelist as $keep) {
            if (strpos($relPath, $keep) === 0) {
                continue 2;
            }
        }

        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
            }
        } else {
            // 新文件或内容不同都复制
            if (!file_exists($destPath) ||
                md5_file($item->getPathname()) !== md5_file($destPath)
            ) {
                copy($item->getPathname(), $destPath);
            }
        }
    }
}

/**
 * 删除目标目录中，源目录没有的文件/目录（清理多余）
 */
function cleanupExtraFiles(string $src, string $dst, array $whitelist) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dst, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        $relPath = substr($item->getPathname(), strlen($dst) + 1);
        $srcPath = $src . '/' . $relPath;

        // 白名单里的文件/目录不删除
        foreach ($whitelist as $keep) {
            if (strpos($relPath, $keep) === 0) {
                continue 2;
            }
        }

        if (!file_exists($srcPath)) {
            if ($item->isDir()) {
                deleteDir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
    }
}

/**
 * 递归删除目录
 */
function deleteDir(string $dir) {
    if (!is_dir($dir)) return;
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileInfo) {
        $fileInfo->isDir() ? rmdir($fileInfo->getPathname()) : unlink($fileInfo->getPathname());
    }
    rmdir($dir);
}
