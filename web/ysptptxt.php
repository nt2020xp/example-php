<?php
// 設定輸出格式為純文字 (UTF-8)
header('Content-Type: text/plain; charset=utf-8');

/**
 * 獲取央視頻頻道資料並轉換為 TXT 格式
 */
function generateTxtPlaylist() {
    $apiUrl = 'https://yangshipin.cn';
    
    // 增加 Header 模擬瀏覽器，避免被 API 封鎖
    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/110.0.0.0 Safari/537.36\r\n",
            "timeout" => 10
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($apiUrl, false, $context);
    
    if (!$response) {
        die("無法獲取資料，請檢查伺服器網路連線。");
    }

    $data = json_decode($response, true);
    
    // 關鍵修正：feedModuleList 是陣列，通常頻道資料在第 0 個索引中
    $channelList = $data['data']['feedModuleList'][0]['dataTvChannelList'] ?? [];

    // 如果第 0 個找不到，嘗試遍歷尋找 (增加保險)
    if (empty($channelList)) {
        foreach ($data['data']['feedModuleList'] as $module) {
            if (!empty($module['dataTvChannelList'])) {
                $channelList = $module['dataTvChannelList'];
                break;
            }
        }
    }

    if (empty($channelList)) {
        die("未找到任何頻道資料。");
    }

    // 輸出分類名稱
    echo "央視頻,#genre#\n";

    foreach ($channelList as $channel) {
        $name = $channel['channelName'];
        $streamId = $channel['streamId'];
        $pid = $channel['pid'];
        
        // 你的代理播放地址格式
        $playUrl = "http://43.156.8{$streamId}&pid={$pid}&q=fhd";

        // 輸出 TXT 標準格式
        echo "{$name},{$playUrl}\n";
    }
}

generateTxtPlaylist();
