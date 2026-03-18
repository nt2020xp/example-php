<?php
error_reporting(0);
ini_set('display_errors', 0);

// ==================== 配置区域 ====================
define('CACHE_DIR', __DIR__ . '/cache/'); // 缓存目录，需手动创建且可写
define('TS_CACHE_TTL', 60); // TS 切片缓存有效期（秒），直播建议 30-60
define('M3U8_CACHE_TTL', 5); // M3U8 文件缓存有效期（秒），0 表示不缓存

// 创建缓存目录（如果不存在）
if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0755, true);
}

// ==================== 工具函数 ====================

/**
 * 检查 APCu 是否可用
 */
function isApcuAvailable()
{
    return function_exists('apcu_fetch') && function_exists('apcu_store') && apcu_enabled();
}

/**
 * 解析相对路径为绝对 URL
 * @param string $baseUrl 基准 URL（如 https://example.com/path/file.m3u8）
 * @param string $relative 相对路径（可能以 http://, /, 或相对形式开头）
 * @return string 绝对 URL
 */
function resolveUrl($baseUrl, $relative)
{
    $parts = parse_url($baseUrl);
    $scheme = $parts['scheme'] ?? 'http';
    $host = $parts['host'] ?? '';
    $path = isset($parts['path']) ? dirname($parts['path']) : '';
    if ($path === '.' || $path === '\\')
        $path = '';

    // 已经是绝对 URL
    if (preg_match('#^https?://#i', $relative)) {
        return $relative;
    }
    // 以 / 开头的绝对路径
    if (strpos($relative, '/') === 0) {
        return $scheme . '://' . $host . $relative;
    }
    // 处理相对路径（包括 ../ 等）
    $baseSegments = explode('/', ltrim($path, '/'));
    $relSegments = explode('/', $relative);
    foreach ($relSegments as $seg) {
        if ($seg === '..') {
            array_pop($baseSegments);
        }
        elseif ($seg !== '.' && $seg !== '') {
            $baseSegments[] = $seg;
        }
    }
    $newPath = implode('/', $baseSegments);
    return $scheme . '://' . $host . '/' . ltrim($newPath, '/');
}

/**
 * 发送 POST 请求（用于 faintv API）
 */
function postRequest($url, $data, $headers = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $headers = array_merge(['Content-Type: application/x-www-form-urlencoded'], $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

/**
 * 发送 GET 请求，返回内容及状态信息（可指定是否使用缓存）
 * 支持 APCu 与文件缓存，优先使用 APCu（若可用）
 */
function getRequestDebug($url, $headers = [], $useCache = false, $cacheTtl = 0)
{
    // 缓存逻辑
    if ($useCache && $cacheTtl > 0) {
        $cacheKey = 'faintv_m3u8_' . md5($url);
        $cacheFile = CACHE_DIR . md5($url) . '.m3u8';

        // 尝试从 APCu 读取
        if (isApcuAvailable()) {
            $body = apcu_fetch($cacheKey, $success);
            if ($success) {
                return ['body' => $body, 'http_code' => 200, 'error' => '', 'from_cache' => true];
            }
        }

        // 尝试从文件读取（APCu 不可用或无缓存）
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
            $body = file_get_contents($cacheFile);
            return ['body' => $body, 'http_code' => 200, 'error' => '', 'from_cache' => true];
        }
    }

    // 动态设置 Host 头
    $urlParts = parse_url($url);
    $host = $urlParts['host'] ?? '';
    // 过滤掉已有的 Host 头
    $headers = array_filter($headers, function ($h) {
        return stripos($h, 'Host:') !== 0;
    });
    $headers[] = 'Host: ' . $host;
    // 移除 Accept-Encoding，避免 cURL 自动解压
    $headers = array_filter($headers, function ($h) {
        return stripos($h, 'Accept-Encoding') === false;
    });

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TCP_NODELAY, true); // 禁用 Nagle 算法
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    $body = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // 缓存成功获取的内容
    if ($useCache && $cacheTtl > 0 && $http_code >= 200 && $http_code < 300 && $body !== false) {
        if (isApcuAvailable()) {
            apcu_store($cacheKey, $body, $cacheTtl);
        }
        else {
            file_put_contents($cacheFile, $body);
        }
    }

    return ['body' => $body, 'http_code' => $http_code, 'error' => $error, 'from_cache' => false];
}

/**
 * 流式输出 TS 切片（支持 APCu 与文件缓存）
 * 若 APCu 可用，则优先使用 APCu（此时将完整载入内存）
 */
function streamTsWithCache($url, $headers)
{
    // 清除之前的输出缓冲区
    ob_end_clean();

    // 动态设置 Host 头
    $urlParts = parse_url($url);
    $host = $urlParts['host'] ?? '';
    $headers = array_filter($headers, function ($h) {
        return stripos($h, 'Host:') !== 0;
    });
    $headers[] = 'Host: ' . $host;
    $headers = array_filter($headers, function ($h) {
        return stripos($h, 'Accept-Encoding') === false;
    });

    $cacheKey = 'faintv_ts_' . md5($url);
    $cacheFile = CACHE_DIR . md5($url) . '.ts';

    // 1. 尝试从 APCu 读取
    if (isApcuAvailable()) {
        $data = apcu_fetch($cacheKey, $success);
        if ($success) {
            header('Content-Type: video/MP2T');
            header('Content-Length: ' . strlen($data));
            echo $data;
            return 200;
        }
    }

    // 2. 尝试从文件读取（APCu 不可用或无缓存）
    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < TS_CACHE_TTL) {
        header('Content-Type: video/MP2T');
        header('Content-Length: ' . filesize($cacheFile));
        readfile($cacheFile);
        return 200;
    }

    // 3. 无有效缓存，从远程拉取
    // 若 APCu 可用，使用 RETURNTRANSFER 取得完整内容后存入 APCu
    if (isApcuAvailable()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TCP_NODELAY, true);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($http_code >= 200 && $http_code < 300 && $data !== false) {
            header('Content-Type: video/MP2T');
            header('Content-Length: ' . strlen($data));
            echo $data;

            // 存入 APCu
            apcu_store($cacheKey, $data, TS_CACHE_TTL);
            return 200;
        }
        else {
            error_log("TS fetch failed: $url , HTTP $http_code, error: $error");
            return $http_code;
        }
    }

    // 4. APCu 不可用，使用原有的流式写法 + 文件缓存
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // 直接输出
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 限制总时间
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_TCP_NODELAY, true);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    // 打开两个输出流：浏览器和缓存文件（临时文件）
    $tmpFile = fopen($cacheFile . '.tmp', 'w');
    $browserOutput = fopen('php://output', 'w');

    // 设置写函数，同时写入两个流
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) use ($tmpFile, $browserOutput) {
        fwrite($tmpFile, $data);
        fwrite($browserOutput, $data);
        return strlen($data);
    });

    // 发送基础头（Content-Length 未知，先不发送）
    header('Content-Type: video/MP2T');

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    fclose($tmpFile);
    fclose($browserOutput);

    // 如果成功，将临时文件重命名为正式缓存
    if ($result && $http_code >= 200 && $http_code < 300) {
        rename($cacheFile . '.tmp', $cacheFile);
    }
    else {
        unlink($cacheFile . '.tmp');
        error_log("TS fetch failed: $url , HTTP $http_code, error: $error");
    }

    return $http_code;
}

/**
 * 获取当前脚本的基础 URL
 */
function getScriptBaseUrl()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    return $protocol . '://' . $host . $script;
}

// ==================== 主逻辑 ====================
$action = isset($_GET['action']) ? $_GET['action'] : 'playlist';
$baseUrl = getScriptBaseUrl();

// 基础请求头（移除了固定 Host 和 Accept-Encoding）
$browserHeaders = [
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept: */*',
    'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8',
    'Connection: keep-alive',
    'Referer: https://ext.faintv.com/',
    'Origin: https://ext.faintv.com',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: cross-site',
    // 如果有 Cookie，请取消下一行注释并填写正确的值
    // 'Cookie: ',
];

if ($action === 'playlist') {
    $channelId = isset($_GET['channelId']) ? $_GET['channelId'] : '19371';
    try {
        // 1. 获取 token
        $initResponse = postRequest('https://ext.faintv.com/api/v1/web/init', ['deviceId' => '']);
        $initData = json_decode($initResponse);
        if (!$initData || !isset($initData->data->token) || !isset($initData->data->expiryDate)) {
            throw new Exception('获取 token 失败');
        }
        $token = $initData->data->token;
        $expiryDate = $initData->data->expiryDate;

        // 2. 获取播放地址
        $streamHeaders = [
            'ExpiryDate: ' . $expiryDate,
            'Token: ' . $token,
            'Referer: https://ext.faintv.com/',
            'Origin: https://ext.faintv.com'
        ];
        $streamResponse = postRequest(
            'https://ext.faintv.com/api/v1/channel/getStreamUrl',
        ['channelId' => $channelId, 'cdnType' => '1'],
            $streamHeaders
        );
        $streamData = json_decode($streamResponse);
        if (!$streamData || !isset($streamData->data->urls[0])) {
            throw new Exception('获取播放地址失败: ' . print_r($streamData, true));
        }
        $originalM3u8Url = $streamData->data->urls[0];

        // 3. 获取主 M3U8 内容（启用缓存）
        $result = getRequestDebug($originalM3u8Url, $browserHeaders, M3U8_CACHE_TTL > 0, M3U8_CACHE_TTL);
        if ($result['body'] === false || $result['http_code'] < 200 || $result['http_code'] >= 300) {
            throw new Exception("无法获取主 M3U8 (HTTP {$result['http_code']})");
        }

        // 4. 修改内容（将里面的所有 URL 替换为代理地址）
        $lines = explode("\n", $result['body']);
        $newLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '#') === 0) {
                $newLines[] = $line;
                continue;
            }
            // 使用 resolveUrl 处理各种相对路径
            $absoluteUrl = resolveUrl($originalM3u8Url, $trimmed);
            $proxyUrl = $baseUrl . '?action=ts&url=' . urlencode($absoluteUrl);
            $newLines[] = $proxyUrl;
        }

        header('Content-Type: application/vnd.apple.mpegurl');
        echo implode("\n", $newLines);

    }
    catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo '错误：' . $e->getMessage();
    }

}
elseif ($action === 'ts') {
    if (!isset($_GET['url'])) {
        http_response_code(400);
        die('Missing url parameter');
    }
    $requestUrl = $_GET['url'];
    if (!preg_match('#^https?://#i', $requestUrl)) {
        http_response_code(400);
        die('Invalid URL');
    }

    // 判断是否为 m3u8 文件（递归代理）
    if (preg_match('/\.m3u8($|\?)/i', $requestUrl)) {
        $result = getRequestDebug($requestUrl, $browserHeaders, M3U8_CACHE_TTL > 0, M3U8_CACHE_TTL);
        if ($result['body'] === false || $result['http_code'] < 200 || $result['http_code'] >= 300) {
            http_response_code(500);
            echo "Failed to fetch m3u8: HTTP " . $result['http_code'] . " " . $result['error'];
            exit;
        }
        $lines = explode("\n", $result['body']);
        $newLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '#') === 0) {
                $newLines[] = $line;
                continue;
            }
            $absoluteUrl = resolveUrl($requestUrl, $trimmed);
            $proxyUrl = $baseUrl . '?action=ts&url=' . urlencode($absoluteUrl);
            $newLines[] = $proxyUrl;
        }
        header('Content-Type: application/vnd.apple.mpegurl');
        echo implode("\n", $newLines);
    }
    else {
        // 是 ts 切片，使用带缓存的流式输出
        $httpCode = streamTsWithCache($requestUrl, $browserHeaders);
        if ($httpCode < 200 || $httpCode >= 300) {
        // 已在函数内记录错误
        }
    }
}
else {
    http_response_code(400);
    echo 'Unknown action';
}
