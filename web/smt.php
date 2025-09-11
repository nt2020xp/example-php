<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); // 调试时显示错误
header('Content-Type: text/plain; charset=utf-8');
date_default_timezone_set("Asia/Shanghai");

// 核心配置
const CONFIG = [
    'upstream'   => [
    'http://198.16.100.186:8278/',
    'http://50.7.92.106:8278/',
    'http://50.7.234.10:8278/',
    'http://50.7.220.170:8278/',
    'http://67.159.6.34:8278/'],
    'list_url'   => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart.txt',
    'backup_url' => 'https://cdn.jsdelivr.net/gh/hostemail/cdn@main/data/smart1.txt',
    'token_ttl'  => 2400, // 40分钟有效期
    'cache_ttl'  => 3600, // 频道列表缓存1小时
    'fallback'   => 'http://vjs.zencdn.net/v/oceans.mp4',
    'clear_key'  => 'leifeng'
];

// 获取当前轮询的上游服务器
function getUpstream() {
    $upstreams = CONFIG['upstream'];

    // 尝试使用 APCu 实现跨请求的轮询
    if (extension_loaded('apcu')) {
        $key = 'upstream_index';
        $index = (int) (apcu_fetch($key) ?: 0);
        $current = $upstreams[$index % count($upstreams)];
        apcu_store($key, $index + 1);
        return $current;
    }

    // 如果没有 APCu，则回退到简单的静态变量轮询 (仅对单次请求有效)
    static $index = 0;
    $current = $upstreams[$index % count($upstreams)];
    $index++;
    return $current;
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
    exit("系统维护中，请稍后重试\n错误详情：" . $e->getMessage());
}

// 获取远端文件，使用cURL并进行错误检查
function fetch_remote_file($url, $timeout = 5) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => $timeout,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => false, // 简化配置，避免自签证书问题
        CURLOPT_HTTPHEADER => [
            'Cache-Control: no-cache',
            "CLIENT-IP: 127.0.0.1",
            "X-FORWARDED-FOR: 127.0.0.1"
        ]
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException("CURL 请求失败: $err (URL: $url)");
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 400) {
        throw new RuntimeException("远程服务器返回 HTTP $http_code 错误 (URL: $url)");
    }

    return $response;
}

// 缓存清除
function clearCache() {
    error_log("[ClearCache] ClientIP:{$_SERVER['REMOTE_ADDR']}, Key:".($_GET['key']??'null'));

    $validKey = $_GET['key'] ?? '';
    $isLocal = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    if (!$isLocal && !hash_equals(CONFIG['clear_key'], $validKey)) {
        header('HTTP/1.1 403 Forbidden');
        exit("权限验证失败\nIP: {$_SERVER['REMOTE_ADDR']}\n密钥状态: ".(empty($validKey)?'未提供':'无效'));
    }

    $results = [];
    $cacheType = '';

    if (extension_loaded('apcu')) {
        $cacheType = 'APCu';
        $results[] = apcu_clear_cache() ? '✅ APCu缓存已清除' : '❌ APCu清除失败';
    } else {
        $results[] = '⚠️ APCu扩展未安装';
    }

    try {
        $list = getChannelList(true);
        if (empty($list)) throw new Exception("频道列表为空");
        $results[] = '📡 频道列表已重建 数量:' . count($list);
        $cacheType = $cacheType ?: '无缓存扩展';
        $results[] = "🔧 使用缓存类型: $cacheType";
    } catch (Exception $e) {
        $results[] = '⚠️ 列表重建失败: ' . $e->getMessage();
    }
    
    header('Cache-Control: no-store');
    exit(implode("\n", $results));
}

// 生成TXT主列表
function sendTXTList() {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    try {
        $channels = getChannelList();
    } catch (Exception $e) {
        header('HTTP/1.1 500 Internal Server Error');
        exit("无法获取频道列表: " . $e->getMessage());
    }

    $baseUrl  = getBaseUrl();
    $script   = basename(__FILE__);
    
    $grouped = [];
    foreach ($channels as $chan) {
        $grouped[$chan['group']][] = $chan;
    }

    $output = '';
    foreach ($grouped as $group => $items) {
        $output .= htmlspecialchars($group) . ",#genre#\n";
        foreach ($items as $chan) {
            $output .= sprintf("%s,%s/%s?id=%s\n",
                htmlspecialchars($chan['name']),
                $baseUrl,
                $script,
                urlencode($chan['id'])
            );
        }
        $output .= "\n";
    }

    header('Content-Disposition: inline; filename="channels_'.time().'.txt"');
    echo trim($output);
}

// 获取频道列表（仅内存缓存）
function getChannelList($forceRefresh = false) {
    if (!$forceRefresh && extension_loaded('apcu')) {
        $cached = apcu_fetch('smart_channels');
        if ($cached !== false) {
            return $cached;
        }
    }

    $raw = fetchWithRetry(CONFIG['list_url'], 3);
    if ($raw === false) {
        $raw = fetchWithRetry(CONFIG['backup_url'], 2);
        if ($raw === false) {
            throw new Exception("所有数据源均不可用");
        }
    }

    $list = [];
    $currentGroup = '默认分组';
    foreach (explode("\n", trim($raw)) as $line) {
        $line = trim($line);
        if (!$line) continue;

        if (strpos($line, '#genre#') !== false) {
            $currentGroup = trim(str_replace(',#genre#', '', $line));
            continue;
        }

        $parts = explode(',', $line);
        $name = trim($parts[0]);

        $id = null;
        if (preg_match('/\/\/:id=(\w+)/', $line, $m)) {
            $id = $m[1];
        } elseif (preg_match('/[?&]id=([^&]+)/', $line, $m)) {
            $id = $m[1];
        }

        if ($id) {
            $list[] = [
                'id'    => $id,
                'name'  => $name,
                'group' => $currentGroup,
                'logo'  => ''
            ];
        }
    }

    if (empty($list)) {
        throw new Exception("频道列表解析失败");
    }

    if (extension_loaded('apcu')) {
        apcu_store('smart_channels', $list, CONFIG['cache_ttl']);
    }

    return $list;
}

// 带重试机制的获取函数
function fetchWithRetry($url, $maxRetries = 3) {
    $retryDelay = 500; // 毫秒
    $lastError = '';
    
    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            $raw = fetch_remote_file($url);
            if ($raw !== false) {
                return $raw;
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
        }
        
        if ($i < $maxRetries - 1) {
            usleep($retryDelay * 1000);
            $retryDelay *= 2; // 指数退避
        }
    }
    
    error_log("[Fetch] 获取失败: $url, 错误: $lastError");
    return false;
}

// 处理频道请求
function handleChannelRequest() {
    $channelId = $_GET['id'];
    $tsFile    = $_GET['ts'] ?? '';
    $token     = manageToken();

    if ($tsFile) {
        proxyTS($channelId, $tsFile);
    } else {
        generateM3U8($channelId, $token);
    }
}

// Token管理V2:无session
function manageToken() {
    $token = $_GET['token'] ?? '';
    
    // 验证现有Token是否有效（含40分钟时效检查）
    if (!empty($token) && validateToken($token)) {
        return $token;
    }
    
    // 生成新Token（32位）
    $newToken = bin2hex(random_bytes(16)) . ':' . time(); // 格式：随机值:时间戳
    
    // TS请求重定向逻辑保持不变
    if (isset($_GET['ts'])) {
        $url = getBaseUrl() . '/' . basename(__FILE__) . '?' . http_build_query([
            'id'    => $_GET['id'],
            'ts'    => $_GET['ts'],
            'token' => $newToken
        ]);
        header("Location: $url");
        exit();
    }
    
    return $newToken;
}

function validateToken($token) {
    // 解析Token格式：随机值:时间戳
    $parts = explode(':', $token);
    if (count($parts) !== 2) return false;
    
    $timestamp = (int)$parts[1];
    $currentTime = time();
    
    // 验证时效性（40分钟内有效）
    return ($currentTime - $timestamp) <= CONFIG['token_ttl']; // 2400秒=40分钟
}


// 生成M3U8播放列表
function generateM3U8($channelId, $token) {
    try {
        $upstream = getUpstream();
        $authUrl = $upstream. "$channelId/playlist.m3u8?" . http_build_query([
            'tid'  => 'mc42afe745533',
            'ct'   => intval(time() / 150),
            'tsum' => md5("tvata nginx auth module/$channelId/playlist.m3u8mc42afe745533" . intval(time() / 150))
        ]);
        
        $content = fetch_remote_file($authUrl);
        
        $baseUrl = getBaseUrl() . '/' . basename(__FILE__);
        $content = preg_replace_callback('/(\S+\.ts)/', function($m) use ($baseUrl, $channelId, $token) {
            return "$baseUrl?id=" . urlencode($channelId) . "&ts=" . urlencode($m[1]) . "&token=" . urlencode($token);
        }, $content);
        
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Content-Disposition: inline; filename="' . $channelId . '.m3u8"');
        echo $content;
    } catch (Exception $e) {
        // 如果获取失败，则重定向到备用地址
        header("Location: " . CONFIG['fallback']);
        exit();
    }
}

// 代理TS流
function proxyTS($channelId, $tsFile) {
    try {
        $upstream = getUpstream();
        $url = $upstream . "$channelId/$tsFile";
        $data = fetch_remote_file($url);
        
        header('Content-Type: video/MP2T');
        header('Content-Length: ' . strlen($data));
        echo $data;
    } catch (Exception $e) {
        header('HTTP/1.1 404 Not Found');
        exit();
    }
}

// 获取基础URL
function getBaseUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
               . "://$_SERVER[HTTP_HOST]";
}
