<?php
/**
 * m3u8/ts 代理修正版
 * 1. 修正 Header 透传（支援拖動進度條）
 * 2. 修正流式轉發緩衝問題
 * 3. 最佳化 M3U8 行處理邏輯
 */

error_reporting(0);
date_default_timezone_set("Asia/Shanghai");

// 強制關閉輸出緩衝，確保流式傳輸立即生效
while (ob_get_level()) ob_end_clean();

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Range');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$name = $_GET['id'] ?? '';
$ts   = $_GET['ts'] ?? '';

if ($name === '') {
    http_response_code(400);
    exit("Missing id");
}

// 過濾參數
$name = preg_replace('~[^a-zA-Z0-9_\-]~', '', $name);
$ts   = $ts ? preg_replace('~[^a-zA-Z0-9_\-\.\/]~', '', $ts) : '';

$serverIPs = ['38.135.24.88'];
$basePort  = 11799;

function selectServer($servers, $name = '') {
    if (empty($servers)) return '';
    $index = !empty($name) ? (abs(crc32($name)) % count($servers)) : array_rand($servers);
    return $servers[$index];
}

function rotatedServers($servers, $first) {
    $idx = array_search($first, $servers, true);
    if ($idx === false) return $servers;
    return array_merge(array_slice($servers, $idx), array_slice($servers, 0, $idx));
}

// ======== 改良版：流式轉發（含 Header 透傳） ========
function curl_stream_to_client($url, array $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_BUFFERSIZE     => 131072, // 128KB
        // 關鍵：將上游的關鍵 Header 轉發給客戶端
        CURLOPT_HEADERFUNCTION => function($ch, $headerLine) {
            $h = strtolower($headerLine);
            if (strpos($h, 'content-type:') === 0 || 
                strpos($h, 'content-length:') === 0 || 
                strpos($h, 'content-range:') === 0 || 
                strpos($h, 'accept-ranges:') === 0) {
                header($headerLine, false);
            }
            return strlen($headerLine);
        },
        CURLOPT_WRITEFUNCTION  => function($ch, $data) {
            echo $data;
            flush(); // 確保資料即時送出
            return strlen($data);
        },
    ]);

    $ok = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$ok && $code < 400, $code];
}

// ======== 獲取 M3U8 文本 ========
function curl_get_text($url, array $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$data, $code];
}

$upHeaders = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $upHeaders[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

$selectedIP = selectServer($serverIPs, $name);
$serversTry = rotatedServers($serverIPs, $selectedIP);

// ==============================
// TS 轉發
// ==============================
if ($ts) {
    foreach ($serversTry as $ip) {
        $url = "http://{$ip}:{$basePort}/{$name}/{$ts}";
        [$ok, $code] = curl_stream_to_client($url, $upHeaders);
        if ($ok) exit;
    }
    http_response_code(404);
    exit;
}

// ==============================
// M3U8 處理
// ==============================
$playlistPath = "/{$name}/playlist.m3u8";
$t    = (string)intval(time() / 150);
$seed = "tvata nginx auth module";
$tid  = "mc42afe834703";
$tsum = md5($seed . $playlistPath . $tid . $t);
$link = http_build_query(["ct" => $t, "tsum" => $tsum, "tid" => $tid]);

$result = null;
foreach ($serversTry as $ip) {
    $url = "http://{$ip}:{$basePort}{$playlistPath}?{$link}";
    [$data, $code] = curl_get_text($url, $upHeaders);
    if ($data && strpos($data, "#EXTM3U") !== false) {
        $result = $data;
        break;
    }
}

if (!$result) {
    header("Location: https://iptv.gv.uy/notv.mp4");
    exit;
}

header('Content-Type: application/vnd.apple.mpegurl');
header('Cache-Control: no-cache');

$self = explode('?', $_SERVER['REQUEST_URI'])[0];
$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $result));
$out = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    if ($line[0] === '#') {
        $out[] = $line;
    } else {
        // 處理 TS 路徑：若是完整 URL 則取其檔案名
        if (stripos($line, 'http') === 0) {
            $pathParts = parse_url($line, PHP_URL_PATH);
            $line = basename($pathParts);
        }
        $out[] = "{$self}?id=" . rawurlencode($name) . "&ts=" . rawurlencode($line);
    }
}

echo implode("\n", $out);
