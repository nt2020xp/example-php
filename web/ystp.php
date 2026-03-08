<?php
/**
 * 央視頻 TXT 自動產生器 (含 cURL, Cache, 動態畫質)
 */

// --- 設定區 ---
$cacheFile = 'ysp_cache.json'; // 快取檔名
$cacheTime = 3600;            // 快取有效時間 (秒)
// 注意：必須使用正確的 API 接口網址
$apiUrl = 'https://yangshipin.cn';

// --- 取得參數 ---
// 預設畫質為 fhd
$quality = isset($_GET['q']) ? htmlspecialchars($_GET['q']) : 'fhd';

// --- 邏輯處理 ---
// 設定輸出格式為純文字
header('Content-Type: text/plain; charset=utf-8');

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

// 3. 輸出 TXT 內容
if (isset($jsonData['data']['feedModuleList'][0]['dataTvChannelList'])) {
    // 輸出 TXT 分組標題 (適用於 TVBox 等軟體)
    echo "央視投屏,#genre#\n";

    foreach ($jsonData['data']['feedModuleList'][0]['dataTvChannelList'] as $channel) {
        $name = $channel['channelName'];
        $sid  = $channel['streamId'];
        $pid  = $channel['pid'];
        
        // 修正播放位址字串串接
        $playUrl = "http://43.156.8{$sid}&pid={$pid}&q={$quality}";

        // 輸出格式：頻道名稱,播放連結
        echo "{$name},{$playUrl}\n";
    }
} else {
    echo "Error: 無法解析頻道資料，請檢查 API 連結或快取。";
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
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36');
    
    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200) ? json_decode($output, true) : null;
}
