<?php
/**
 * m3u8/ts 代理完整版
 * 1. 無參數時：輸出所有頻道 M3U8 清單
 * 2. 帶 id 參數：代理播放列表並重寫 TS 路徑
 * 3. 帶 ts 參數：流式轉發 TS 文件並透傳 Range 頭（支援進度條）
 */

error_reporting(0);
date_default_timezone_set("Asia/Shanghai");

// 強制關閉 PHP 輸出緩衝，確保流式傳輸即時性
while (ob_get_level()) ob_end_clean();

// --- 配置區 ---
$serverIPs = ['38.135.24.88']; // 你的後端伺服器列表
$basePort  = 11799;            // 後端埠號
$seed      = "tvata nginx auth module";
$tid       = "mc42afe834703";

// 頻道定義 (ID => 頻道名稱)
$channels = [
    'ch1' => 'HBO HD',
    'ch2' => 'Discovery Channel',
    'ch3' => 'CNN International',
    // 這裡新增你的頻道 ID
];

// --- 參數處理 ---
$name = $_GET['id'] ?? '';
$ts   = $_GET['ts'] ?? '';
$selfUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0];

// --- CORS 設定 ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Range');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ==============================
// 場景 1：無參數時輸出全部頻道
// ==============================
if ($name === '') {
    header('Content-Type: application/vnd.apple.mpegurl');
    echo "#EXTM3U\n";
    foreach ($channels as $id => $title) {
        // 使用 Master Playlist 格式 (M3U8)
        echo "#EXT-X-STREAM-INF:BANDWIDTH=2560000,NAME=\"$title\"\n";
        echo "{$selfUrl}?id={$id}\n";
    }
    exit;
}

// 安全過濾
$name = preg_replace('~[^a-zA-Z0-9_\-]~', '', $name);
$ts   = $ts ? preg_replace('~[^a-zA-Z0-9_\-\.\/]~', '', $ts) : '';

// 伺服器選擇邏輯 (一致性雜湊)
function selectServer($servers, $name) {
    if (empty($servers)) return '';
    $index = abs(crc32($name)) % count($servers);
    return $servers[$index];
}

function rotatedServers($servers, $first) {
    $idx = array_search($first, $servers, true);
    if ($idx === false) return $servers;
    return array_merge(array_slice($servers, $idx), array_slice($servers, 0, $idx));
}

$selectedIP = selectServer($serverIPs, $name);
$serversTry = rotatedServers($serverIPs, $selectedIP);

$upHeaders = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $upHeaders[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// ==============================
// 場景 2：TS 流式轉發
// ==============================
if ($ts) {
    foreach ($serversTry as $ip) {
        $url = "http://{$ip}:{$basePort}/{$name}/{$ts}";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $upHeaders,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_BUFFERSIZE     => 131072, // 128KB 緩衝
            CURLOPT_HEADERFUNCTION => function($ch, $headerLine) {
                // 透傳關鍵 Header 給播放器
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
                flush(); // 強制送出
                return strlen($data);
            },
        ]);

        $ok = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($ok && $code < 400) exit;
    }
    http_response_code(404);
    exit("TS Not Found");
}

// ==============================
// 場景 3：M3U8 代理與重寫
// ==============================
$playlistPath = "/{$name}/playlist.m3u8";
$t    = (string)intval(time() / 150);
$tsum = md5($seed . $playlistPath . $tid . $t);
$query = http_build_query(["ct" => $t, "tsum" => $tsum, "tid" => $tid]);

$m3u8Content = null;
foreach ($serversTry as $ip) {
    $url = "http://{$ip}:{$basePort}{$playlistPath}?{$query}";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $upHeaders,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($data && strpos($data, "#EXTM3U") !== false) {
        $m3u8Content = $data;
        break;
    }
}

if (!$m3u8Content) {
    header("Location: https://iptv.gv.uy"); // 失敗跳轉
    exit;
}

header('Content-Type: application/vnd.apple.mpegurl');
header('Cache-Control: no-cache');

// 逐行解析重寫 TS URL
$lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $m3u8Content));
$output = [];

foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line)) continue;

    if ($line[0] === '#') {
        $output[] = $line;
    } else {
        // 如果是完整 URL，只取路徑最後的檔案名
        if (stripos($line, 'http') === 0) {
            $pathParts = parse_url($line, PHP_URL_PATH);
            $line = basename($pathParts);
        }
        $output[] = "{$selfUrl}?id=" . rawurlencode($name) . "&ts=" . rawurlencode($line);
    }
}

echo implode("\n", $output);
