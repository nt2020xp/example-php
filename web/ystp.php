<?php
/**
 * 央視頻 TXT 播放列表產生器 (含 cURL, Cache, 動態畫質)
 */

// --- 設定區 ---
$cacheFile = 'ysp_cache.json'; // 快取檔名
$cacheTime = 3600;            // 快取有效時間 (秒)
$apiUrl = 'https://yangshipin.cn';

// --- 取得畫質參數 ---
$quality = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : 'fhd';

// --- 設定輸出格式為純文字 ---
header('Content-Type: text/plain; charset=utf-8');

// 1. 快取邏輯
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $jsonData = json_decode(file_get_contents($cacheFile), true);
} else {
    $jsonData = fetchWithCurl($apiUrl);
    if ($jsonData) {
        file_put_contents($cacheFile, json_encode($jsonData));
    }
}

// 2. 輸出 TXT 內容
if (isset($jsonData['data']['feedModuleList'][0]['dataTvChannelList'])) {
    // 輸出分組名稱
    echo "央視投屏,#genre#\n";

    foreach ($jsonData['data']['feedModuleList'][0]['dataTvChannelList'] as $channel) {
        $name = $channel['channelName'];
        $sid  = $channel['streamId'];
        $pid  = $channel['pid'];
        
        // 組合播放地址
        $playUrl = "http://43.156.8{$sid}&pid={$pid}&q={$quality}";

        // 輸出格式：頻道名稱,播放網址
        echo "{$name},{$playUrl}\n";
    }
} else {
    echo "資料獲取失敗，請檢查 API 或 cURL 設定。";
}

/**
 * cURL 請求函式
 */
function fetchWithCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/110.0.0.0 Safari/537.36');
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? json_decode($output, true) : null;
}
