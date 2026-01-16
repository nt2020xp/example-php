<?php
/**
 * 单文件 m3u8/ts 代理 + 生成完整订阅 M3U（强制 token 验证 + 多 tid 轮换）
 * 访问 https://smart.freenet.cloudns.org/smt.php?token=123456 直接输出完整 M3U 列表
 * 文件名 smt.m3u，频道之间无多余空行
 * 有 id 参数时 → 代理单个频道，支持多个 tid 自动 failover
 * 频道数据从同目录 smt.json 读取
 */
error_reporting(0);
date_default_timezone_set("Asia/Shanghai");

// -------------------------- 配置区 --------------------------
$serverHosts = [
    'smt.goiptv.us.ci',
    // 可添加更多上游备用域名
];

$seed = "tvata nginx auth module";

// 支持多个 tid，轮换使用（建议至少 2-3 个，越多越稳）
$tid_list = [
    "mc42afe834703",   // 第一个（原版）
    "mc42afe745533",   // 第二个（你提供的）
    // 可以继续添加更多 tid，例如：
    // "mc42afe123456",
    // "mc42afe789012",
];

// 访问 token（必须匹配）
$required_token = "123456";     // 自己随便设置

// 站点域名（自动获取）
$self_domain = $_SERVER['HTTP_HOST'] ?? 'smart.freenet.cloudns.org';
$protocol = 'https://';

// -------------------------- token 验证 --------------------------
$input_token = $_GET['token'] ?? '';

if ($input_token === '' || $input_token !== $required_token) {
    http_response_code(403);
    header('Content-Type: text/plain');
    die("Access denied. Missing or invalid token.");
}

// -------------------------- 获取参数 --------------------------
$name = $_GET['id'] ?? '';
$ts = $_GET['ts'] ?? '';

// -------------------------- 无 id 参数 → 输出完整 M3U 列表 --------------------------
if (empty($name)) {
    header('Content-Type: application/vnd.apple.mpegurl; charset=utf-8');
    header('Content-Disposition: attachment; filename="smt.m3u"');
    header('Cache-Control: no-cache, must-revalidate');

    $json_file = __DIR__ . '/smt.json';
    if (!file_exists($json_file)) {
        http_response_code(500);
        echo "#EXTM3U\n#ERROR: smt.json not found in root directory";
        exit;
    }

    $channels = json_decode(file_get_contents($json_file), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($channels)) {
        http_response_code(500);
        echo "#EXTM3U\n#ERROR: Invalid smt.json format";
        exit;
    }

    echo "#EXTM3U\n";
    echo "#EXTM3U x-tvg-url=\"https://epg.iill.top/epg.xml\"\n";

    foreach ($channels as $ch) {
        $id = $ch['id'] ?? '';
        $ch_name = $ch['channel_name'] ?? ($ch['tvg_name'] ?? '未知频道');
        $logo = $ch['tvg_logo'] ?? '';
        $group = $ch['group_title'] ?? '未分组';
        $tvgid = $ch['tvg_id'] ?? $ch_name;

        if (empty($id)) continue;

        $proxy_url = $protocol . $self_domain . '/smt.php?id=' . urlencode($id) . '&token=' . urlencode($required_token);

        echo "#EXTINF:-1";
        if ($logo)  echo " tvg-logo=\"$logo\"";
        if ($tvgid) echo " tvg-id=\"$tvgid\"";
        if ($group) echo " group-title=\"$group\"";
        echo ",$ch_name\n";
        echo $proxy_url . "\n";
    }

    exit;
}

// -------------------------- 有 id 参数 → 代理单个频道（支持多 tid 轮换） --------------------------

// CORS / OPTIONS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Range');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// 安全过滤
$name = preg_replace('~[^a-zA-Z0-9_\-]~', '', $name);
$ts = $ts ? preg_replace('~[^a-zA-Z0-9_\-\.\/\?\&\=\%]~', '', $ts) : '';

// 选服务器 + failover
function selectServer($servers, $name = '') {
    if (!empty($name)) {
        $index = abs(crc32($name)) % count($servers);
        return $servers[$index];
    }
    return $servers[array_rand($servers)];
}

function rotatedServers($servers, $first) {
    $idx = array_search($first, $servers, true);
    if ($idx === false) return $servers;
    return array_merge(array_slice($servers, $idx), array_slice($servers, 0, $idx));
}

$selectedHost = selectServer($serverHosts, $name);
$serversTry = rotatedServers($serverHosts, $selectedHost);

// 构建 TS URL
function build_ts_url($host, $name, $ts) {
    if ($ts !== '' && $ts[0] === '/') {
        return "http://{$host}{$ts}";
    }
    return "http://{$host}/{$name}/{$ts}";
}

// curl 流式转发 TS
function curl_stream_to_client($url, array $headers, $timeout = 10) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_BUFFERSIZE     => 262144,
        CURLOPT_DNS_CACHE_TIMEOUT => 300,
        CURLOPT_WRITEFUNCTION  => function($ch, $data) {
            echo $data;
            @ob_flush();
            flush();
            return strlen($data);
        },
    ]);

    $ok = curl_exec($ch);
    $errno = curl_errno($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($ok === false || $errno !== 0) return [false, 0];
    return [$code >= 200 && $code < 300, $code];
}

// 获取 m3u8 文本
function curl_get_text($url, array $headers, $timeout = 8) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_DNS_CACHE_TIMEOUT => 300,
    ]);

    $data = curl_exec($ch);
    $errno = curl_errno($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0 || $data === false || $code === 404) {
        return [null, $code ?: 0];
    }
    return [$data, $code];
}

// 上游请求头
$upHeaders = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $upHeaders[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// TS 代理模式
if ($ts !== '') {
    header('Content-Type: video/mp2t');
    header('Cache-Control: public, max-age=30');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');
    header('Accept-Ranges: bytes');

    foreach ($serversTry as $host) {
        $url = build_ts_url($host, $name, $ts);
        [$ok, $code] = curl_stream_to_client($url, $upHeaders, 10);
        if ($ok) exit;
    }

    http_response_code(404);
    echo "Error: TS not found on all servers.";
    exit;
}

// M3U8 代理模式（单个频道）——关键修改：多 tid 轮换
header('Content-Type: application/vnd.apple.mpegurl');
header('Cache-Control: public, max-age=2');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 2) . ' GMT');

$playlistPath = "/{$name}/playlist.m3u8";
$result = null;

foreach ($serversTry as $host) {
    foreach ($tid_list as $current_tid) {
        $t = (string)intval(time() / 150);
        $str = $seed . $playlistPath . $current_tid . $t;
        $tsum = md5($str);
        $link = http_build_query(["ct" => $t, "tsum" => $tsum]);

        $url = "http://{$host}{$playlistPath}?tid={$current_tid}&{$link}";

        [$data, $code] = curl_get_text($url, $upHeaders, 8);
        if (!empty($data) && $code >= 200 && $code < 300 && strpos($data, "EXTM3U") !== false) {
            $result = $data;
            break 2;  // 成功后跳出两层循环
        }
    }
}

if ($result === null) {
    header("Location: http://vjs.zencdn.net/v/oceans.mp4");
    exit;
}

// 重写 ts 链接（带 token）
$lines = preg_split("/\r\n|\n|\r/", $result);
$out = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;
    if ($line[0] === '#') {
        $out[] = $line;
        continue;
    }

    if (stripos($line, '.ts') !== false) {
        $tsName = $line;

        if (stripos($tsName, 'http://') === 0 || stripos($tsName, 'https://') === 0) {
            $p = parse_url($tsName);
            $path = $p['path'] ?? '';
            $tsName = $path ?: '';
            if (!empty($p['query'])) $tsName .= '?' . $p['query'];
        }

        $out[] = $_SERVER['PHP_SELF'] . "?id=" . rawurlencode($name) . "&ts=" . rawurlencode($tsName) . "&token=" . urlencode($required_token);
    } else {
        $out[] = $line;
    }
}

echo implode("\n", $out) . "\n";
exit;
