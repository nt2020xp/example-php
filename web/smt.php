<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0); 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set("Asia/Shanghai");

const CONFIG = [
    'upstream'   => ['http://66.90.99.154:8278/'],
    'list_url'   => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart.txt',
    'backup_url' => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart1.txt', 
    'token_ttl'  => 2400,  
    'cache_ttl'  => 3600, 
    'clear_key'  => 'leifeng'
];

function getUpstream() {
    static $index = 0;
    $upstreams = CONFIG['upstream'];
    return $upstreams[$index % count($upstreams)];
}

try {
    if (isset($_GET['action']) && $_GET['action'] === 'clear_cache') {
        clearCache();
    } elseif (!isset($_GET['id'])) {
        sendTXTList();
    } else {
        handleChannelRequest();
    }
} catch (Exception $e) {
    header('HTTP/1.1 503 Service Unavailable');
    exit("System Error");
}

function sendTXTList() {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    try {
        $channels = getChannelList();
    } catch (Exception $e) {
        exit("List Error");
    }
    $baseUrl = getBaseUrl();
    $script  = basename(__FILE__);
    $grouped = [];
    foreach ($channels as $chan) {
        $grouped[$chan['group']][] = $chan;
    }
    $output = '';
    foreach ($grouped as $group => $items) {
        $output .= $group . ",#genre#\n";
        foreach ($items as $chan) {
            $output .= sprintf("%s,%s/%s?id=%s\n",
                $chan['name'],
                $baseUrl,
                $script,
                urlencode($chan['id'])
            );
        }
    }
    header('Content-Disposition: inline; filename="channels.txt"');
    echo trim($output);
}

function handleChannelRequest() {
    $channelId = $_GET['id'];
    $tsFile    = $_GET['ts'] ?? '';
    
    // 獲取或生成 Token
    $token = manageToken(!empty($tsFile)); 

    if ($tsFile) {
        proxyTS($channelId, $tsFile);
    } else {
        generateM3U8($channelId, $token);
    }
}

function manageToken($isTsRequest = false) {
    $token = $_GET['token'] ?? '';
    $isValid = false;
    
    if (!empty($token)) {
        $parts = explode(':', $token);
        if (count($parts) === 2) {
            $timestamp = (int)$parts[1];
            if ((time() - $timestamp) <= (CONFIG['token_ttl'] + 600)) { // 額外寬限10分鐘
                $isValid = true;
            }
        }
    }
    
    if ($isValid) return $token;
    if ($isTsRequest) return $token; // TS 請求不強制刷新 Token 防止中斷
    
    return bin2hex(random_bytes(8)) . ':' . time();
}

function generateM3U8($channelId, $token) {
    $upstream = getUpstream();
    $timeStep = intval(time() / 150);
    $authUrl = $upstream . "$channelId/playlist.m3u8?" . http_build_query([
        'tid'  => 'mc42afe745533',
        'ct'   => $timeStep,
        'tsum' => md5("tvata nginx auth module/$channelId/playlist.m3u8mc42afe745533" . $timeStep)
    ]);
    
    $content = fetchUrl($authUrl);
    
    if (!$content || strpos($content, 'EXTM3U') === false) {
        header('HTTP/1.1 404 Not Found');
        exit("Stream Not Found");
    }
    
    $baseUrl = getBaseUrl() . '/' . basename(__FILE__);
    // 替換 TS 鏈接，帶上 ID 和 Token
    $content = preg_replace_callback('/(\S+\.ts)/', function($m) use ($baseUrl, $channelId, $token) {
        return "$baseUrl?id=" . urlencode($channelId) . "&ts=" . urlencode($m[1]) . "&token=" . urlencode($token);
    }, $content);
    
    header('Content-Type: application/vnd.apple.mpegurl');
    echo $content;
}

function proxyTS($channelId, $tsFile) {
    $url = getUpstream() . "$channelId/$tsFile";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
            "X-Forwarded-For: 127.0.0.1" 
        ]
    ]);

    header('Content-Type: video/MP2T');
    // 使用流式輸出，減少內存佔用
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        header('HTTP/1.1 404 Not Found');
    }
    exit;
}

function fetchUrl($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 7,
        CURLOPT_SSL_VERIFYPEER => false,
        CURL
