<?php
// 1. 原始資料定義（放在最前面，確保後續邏輯都能抓到）
$documentContent = <<<DOC
# 央視CCTV,#genre#
CCTV 1 綜合,http://127.0.0.1:43251/aHR0cDovLzQ1Ljg4LjE1MS42Njo5NTYwLzAxY2N0djEvaW5kZXgubTN1OD92LmNoYW5uZWxpZD0xMTg0JnYudHlwZT0xJmlwcz01MS43OS4xNy4zODoxOTk4NA==
# 可以在此繼續添加頻道...
DOC;

// 2. 解析頻道清單
$channels = [];
$lines = explode("\n", $documentContent);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#genre#') !== false || strpos($line, '#') === 0) continue;
    
    if (strpos($line, ',') !== false) {
        list($chanName, $url) = explode(',', $line, 2);
        $channels[trim($chanName)] = trim($url);
    }
}

// 3. 處理 API 請求動作 (action)
$action = isset($_GET['action']) ? $_GET['action'] : 'play';

if ($action === 'list') {
    header('Content-Type: application/json; charset=utf-8');
    $channelList = [];
    $baseUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
    foreach ($channels as $name => $url) {
        $channelList[] = [
            'name' => $name,
            'play_url' => $baseUrl . '?channel=' . urlencode($name)
        ];
    }
    echo json_encode($channelList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 4. 處理播放邏輯 (channel)
$channel = isset($_GET['channel']) ? trim($_GET['channel']) : '';
if (empty($channel)) {
    header('HTTP/1.1 400 Bad Request');
    die('錯誤：請指定頻道參數或使用 action=list 查看清單');
}

if (!isset($channels[$channel])) {
    header('HTTP/1.1 404 Not Found');
    die('錯誤：未找到頻道 "' . htmlspecialchars($channel) . '"');
}

$targetUrl = $channels[$channel];

// 5. 執行 cURL 代理
header('Content-Type: application/vnd.apple.mpegurl');
header('Access-Control-Allow-Origin: *');

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => false, // 直接輸出
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) IPTV/1.0',
]);

if (!curl_exec($ch)) {
    header('HTTP/1.1 502 Bad Gateway');
    echo '代理請求失敗：' . curl_error($ch);
}
curl_close($ch);
exit;
