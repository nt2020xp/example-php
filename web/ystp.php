<?php
/**
 * 央視頻 M3U 自動產生器 (含 cURL, Cache, 動態畫質)
 */

// --- 設定區 ---
$cacheFile = 'ysp_cache.json'; // 快取檔名
$cacheTime = 3600;            // 快取有效時間 (秒)，預設 1 小時
$apiUrl = 'https://yangshipin.cn';

// --- 取得參數 ---
// 預設畫質為 fhd，可透過 playlist.php?q=shd 更改
$quality = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : 'fhd';

// --- 邏輯處理 ---
header('Content-Type: application/x-mpegurl; charset=utf-8');
header('Content-Disposition: attachment; filename="yangshipin.m3u"');

// 1. 檢查快取是否有效
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $jsonData = json_decode(file_get_contents($cacheFile), true);
} else {
    // 2. 使用 cURL 抓取新資料
    $jsonData = fetchWithCurl($apiUrl);
    if ($jsonData) {
        file_put_contents($cacheFile, json_encode($jsonData)); // 存入快取
    }
}

// 3. 輸出 M3U 內容
echo "#EXTM3U\n";

if (isset($jsonData['data']['feedModuleList'][0]['dataTvChannelList'])) {
    foreach ($jsonData['data']['feedModuleList'][0]['dataTvChannelList'] as $channel) {
        $name = $channel['channelName'];
        $logo = $channel['tvLogo'];
        $sid  = $channel['streamId'];
        $pid  = $channel['pid'];
        
        // 組合成播放位址
        $playUrl = "http://43.156.8{$sid}&pid={$pid}&q={$quality}";

        echo "#EXTINF:-1 tvg-logo=\"{$logo}\" group-title=\"央視投屏\",{$name}\n";
        echo "{$playUrl}\n";
    }
} else {
    echo "# Error: 無法解析頻道資料\n";
}

/**
 * cURL 請求函式
 */
function fetchWithCurl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 略過 SSL 檢查
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? json_decode($output, true) : null;
}
