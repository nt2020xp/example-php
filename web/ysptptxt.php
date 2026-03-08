<?php
// 強制輸出為純文字格式
header('Content-Type: text/plain; charset=utf-8');

/**
 * 抓取央視頻數據並轉為 TXT 格式
 */
function getCCTVPlaylist() {
    // 央視頻 PC 端分頁 API
    $url = "https://yangshipin.cn";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // 必須模擬瀏覽器，否則 API 可能回傳 403 或 404
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36');
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) {
        die("無法讀取 API (HTTP Code: $httpCode)。請檢查伺服器是否能連外網。");
    }

    $json = json_decode($res, true);
    
    // 定位頻道列表 (修正索引問題)
    $modules = $json['data']['feedModuleList'] ?? [];
    $channels = [];

    foreach ($modules as $module) {
        if (isset($module['dataTvChannelList']) && !empty($module['dataTvChannelList'])) {
            $channels = $module['dataTvChannelList'];
            break;
        }
    }

    if (empty($channels)) {
        die("API 解析成功，但找不到頻道清單。可能 API 結構已變更。");
    }

    // 開始輸出 TXT 內容
    echo "央視頻,#genre#\n";
    foreach ($channels as $item) {
        $name = $item['channelName'];
        $sid  = $item['streamId'];
        $pid  = $item['pid'];
        
        // 這裡套用你的代理服務器地址
        $playUrl = "http://43.156.8{$sid}&pid={$pid}&q=fhd";
        
        echo "{$name},{$playUrl}\n";
    }
}

getCCTVPlaylist();
?>
