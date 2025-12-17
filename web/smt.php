<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); 
header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set("Asia/Shanghai");

// 核心配置
const CONFIG = [
    'upstream'   => ['http://66.90.99.154:8278/'],
    'list_url'   => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart.txt',
    'backup_url' => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart1.txt', 
    'token_ttl'  => 2400,  
    'cache_ttl'  => 3600,  
    'fallback'   => 'http://vjs.zencdn.net/v/oceans.mp4', 
    'clear_key'  => 'leifeng'
];

// 獲取當前輪詢的上游伺服器
function getUpstream() {
    static $index = 0;
    $upstreams = CONFIG['upstream'];
    $current = $upstreams[$index % count($upstreams)];
    $index++;
    return rtrim($current, '/') . '/'; // 確保以 / 結尾
}

// 主路由控制
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
    exit("系統維護中，請稍後重試\n錯誤詳情：" . $e->getMessage());
}

// 修改後的遠程獲取函數
function fetch_remote_file($url, $timeout = 10) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_HTTPHEADER => [
            'Cache-Control: no-cache',
            'User-Agent: Mozilla/5.0'
        ]
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    return ($http_code == 200) ? $response : false;
}

// 緩存清除
function clearCache() {
    $validKey = $_GET['key'] ?? '';
    if (!hash_equals(CONFIG['clear_key'], $validKey)) {
        header('HTTP/1.1 403 Forbidden');
        exit("權限驗證失敗");
    }

    if (extension_loaded('apcu')) {
        apcu_clear_cache();
        echo "✅ APCu緩存已清除\n";
    }
    
    try {
        getChannelList(true);
        echo "📡 頻道列表已重建";
    } catch (Exception $e) {
        echo "⚠️ 列表重建失敗: " . $e->getMessage();
    }
    exit;
}

// 生成TXT主列表
function sendTXTList() {
    try {
        $channels = getChannelList();
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Error');
        exit("無法獲取列表: " . $e->getMessage());
    }

    $baseUrl = getBaseUrl();
    $script = basename(__FILE__);
    $output = '';
    
    $grouped = [];
    foreach ($channels as $chan) {
        $grouped[$chan['group']][] = $chan;
    }

    foreach ($grouped as $group => $items) {
        $output .= "$group,#genre#\n";
        foreach ($items as $chan) {
            $output .= sprintf("%s,%s/%s?id=%s\n", $chan['name'], $baseUrl, $script, urlencode($chan['id']));
        }
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo trim($output);
}

// 獲取頻道列表
function getChannelList($forceRefresh = false) {
    if (!$forceRefresh && extension_loaded('apcu')) {
        $cached = apcu_fetch('smart_channels');
        if ($cached !== false) return $cached;
    }

    $raw = fetchWithRetry(CONFIG['list_url']);
    if (!$raw) $raw = fetchWithRetry(CONFIG['backup_url']);
    if (!$raw) throw new Exception("數據源不可用");

    $list = [];
    $currentGroup = '默認分組';
    foreach (explode("\n", $raw) as $line) {
        $line = trim($line);
        if (!$line) continue;
        if (strpos($line, '#genre#') !== false) {
            $currentGroup = trim(str_replace(',#genre#', '', $line));
            continue;
        }
        if (preg_match('/(.*?),.*[?&]id=([^&]+)/', $line, $m)) {
            $list[] = ['id' => $m[2], 'name' => trim($m[1]), 'group' => $currentGroup];
        }
    }

    if (extension_loaded('apcu')) apcu_store('smart_channels', $list, CONFIG['cache_ttl']);
    return $list;
}

function fetchWithRetry($url, $maxRetries = 3) {
    for ($i = 0; $i < $maxRetries; $i++) {
        $data = fetch_remote_file($url);
        if ($data !== false) return $data;
        usleep(500000);
    }
    return false;
}

// 處理頻道請求
function handleChannelRequest() {
    $channelId = $_GET['id'] ?? '';
    $tsFile    = $_GET['ts'] ?? '';
    $token     = manageToken();

    if ($tsFile) {
        proxyTS($channelId, $tsFile);
    } else {
        generateM3U8($channelId, $token);
    }
}

function manageToken() {
    $token = $_GET['token'] ?? '';
    if (!empty($token) && validateToken($token)) return $token;
    
    $newToken = bin2hex(random_bytes(8)) . ':' . time();
    if (isset($_GET['ts'])) {
        $url = getBaseUrl() . '/' . basename(__FILE__) . '?' . http_build_query([
            'id' => $_GET['id'], 'ts' => $_GET['ts'], 'token' => $newToken
        ]);
        header("Location: $url");
        exit();
    }
    return $newToken;
}

function validateToken($token) {
    $parts = explode(':', $token);
    if (count($parts) !== 2) return false;
    return (time() - (int)$parts[1]) <= CONFIG['token_ttl'];
}

function generateM3U8($channelId, $token) {
    $upstream = getUpstream();
    $timeKey = intval(time() / 150);
    $authUrl = $upstream . "$channelId/playlist.m3u8?" . http_build_query([
        'tid'  => 'mc42afe745533',
        'ct'   => $timeKey,
        'tsum' => md5("tvata nginx auth module/$channelId/playlist.m3u8mc42afe745533$timeKey")
    ]);
    
    $content = fetch_remote_file($authUrl);
    if (!$content || strpos($content, '#EXTM3U') === false) {
        header("Location: " . CONFIG['fallback']);
        exit();
    }
    
    $baseUrl = getBaseUrl() . '/' . basename(__FILE__);
    // 優化匹配規律，確保只替換 TS 文件名
    $content = preg_replace_callback('/^[^#\s]+\.ts(\?.*)?$/m', function($m) use ($baseUrl, $channelId, $token) {
        return "$baseUrl?id=" . urlencode($channelId) . "&ts=" . urlencode($m[0]) . "&token=" . urlencode($token);
    }, $content);
    
    header('Content-Type: application/vnd.apple.mpegurl');
    echo $content;
}

function proxyTS($channelId, $tsFile) {
    $upstream = getUpstream();
    $url = $upstream . "$channelId/$tsFile";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => ["X-Forwarded-For: 127.0.0.1"]
    ]);
    $data = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code == 200) {
        header('Content-Type: video/MP2T');
        echo $data;
    } else {
        header('HTTP/1.1 404 Not Found');
    }
}

function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    return "$protocol://$_SERVER[HTTP_HOST]";
}
