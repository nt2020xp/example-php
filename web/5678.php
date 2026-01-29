<?php
/*
使用方法 litv.php?id=4gtv-4gtv001
无参数时返回完整频道列表
*/

// 解決跨域問題，方便網頁播放器調用
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain; charset=utf-8');

$channels = [    
    '4gtv-4gtv072' => ['TVBS新聞', 1, 6],
    '4gtv-4gtv152' => ['東森新聞', 1, 7],
    '4gtv-4gtv153' => ['東森財經', 1, 9],
    'litv-ftv13' => ['民視新聞', 1, 9],
    '4gtv-4gtv009' => ['中天新聞', 2, 9],
    '4gtv-4gtv052' => ['華視新聞', 1, 6],
    '4gtv-4gtv074' => ['中視新聞', 1, 6],
    '4gtv-4gtv051' => ['台視新聞', 1, 6],    
    'litv-longturn14' => ['寰宇新聞', 1, 7],
    '4gtv-4gtv156' => ['寰宇台灣', 1, 8],          
    '4gtv-4gtv041' => ['華視', 1, 7],
    '4gtv-4gtv040' => ['中視', 1, 7],
    '4gtv-4gtv066' => ['台視', 1, 7],
    '4gtv-4gtv155' => ['民視', 1, 7],  
    'litv-ftv07' => ['民視旅遊台', 1, 9],
    '4gtv-4gtv076' => ['亞洲旅遊台', 1, 7],
    'litv-xinchuang19' => ['Smart知識台', 5, 9],
    '4gtv-4gtv047' => ['靖天日本台', 1, 2],
    '4gtv-4gtv062' => ['靖天育樂台', 1, 9],
    '4gtv-4gtv055' => ['靖天映畫台', 1, 9],  
    '4gtv-4gtv063' => ['靖天國際台', 1, 8],
    '4gtv-4gtv065' => ['靖天資訊台', 1, 9],
    '4gtv-4gtv061' => ['靖天電影台', 1, 7],
    '4gtv-4gtv046' => ['靖天綜合台', 1, 7],
    '4gtv-4gtv058' => ['靖天戲劇台', 1, 9],
    '4gtv-4gtv054' => ['靖天歡樂台', 1, 9],
    '4gtv-4gtv045' => ['靖洋戲劇台', 1, 7],   
    '4gtv-4gtv044' => ['靖天卡通台', 1, 9],
    '4gtv-4gtv057' => ['靖洋卡通台', 1, 7],     
    'litv-xinchuang12' => ['龍華偶像台',10003,20000],
    'litv-xinchuang01' => ['龍華卡通台',10002,20000],
    'litv-xinchuang18' => ['龍華戲劇台',10003,20000],
    'litv-xinchuang11' => ['龍華日韓台',10003,20000],
    'litv-xinchuang21' => ['龍華經典台',10003,20000],
    'litv-xinchuang03' => ['龍華電影台',10003,20000],
    'litv-xinchuang02' => ['龍華洋片台',10003,20000],
    '4gtv-4gtv073' => ['TVBS', 1, 6],  
    '4gtv-4gtv067' => ['TVBS精采台', 1, 9],
    '4gtv-4gtv068' => ['TVBS歡樂台', 1, 8],     
    '4gtv-4gtv064' => ['中視菁采台', 1, 9],   
    '4gtv-4gtv080' => ['中視經典台', 1, 8],  
    '4gtv-4gtv001' => ['民視台灣台', 1, 7],
    '4gtv-4gtv003' => ['民視第一台', 1, 7],
    '4gtv-4gtv004' => ['民視綜藝台', 1, 9],
    'litv-ftv09' => ['民視影劇台', 1, 6],      
    '4gtv-4gtv049' => ['采昌影劇台', 1, 9],   
    '4gtv-4gtv042' => ['公視戲劇', 1, 7],
    'litv-xinchuang22' => ['台灣戲劇台', 5, 2],    
    '4gtv-4gtv011' => ['影迷數位電影台', 1, 7],
    '4gtv-4gtv013' => ['視納華仁紀實頻道', 1, 7],
    '4gtv-4gtv070' => ['愛爾達娛樂台', 1, 9],
    '4gtv-4gtv018' => ['達文西頻道', 1, 7],         
];

// 取得當前 URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$baseUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

// 無 ID 參數時列出所有清單
if (!isset($_GET['id'])) {
    foreach ($channels as $id => $data) {
        echo $data[0] . ',' . $baseUrl . '?id=' . $id . "\n";
    }
    exit;
}

$id = $_GET['id'];
if (!isset($channels[$id])) {
    http_response_code(404);
    echo "頻道不存在";
    exit;
}

// 取得頻道對應參數
$videoParam = $channels[$id][1];
$audioParam = $channels[$id][2];

/**
 * 修正後的時間戳邏輯
 * LiTV/4GTV Hinet CDN 通常以 4 秒為一個 TS 切片
 */
$duration = 4;
$now = time();
// 計算序列號：當前時間除以 4，減去 6 是為了提供一點緩衝（延後約 24 秒），確保檔案已生成
$timestamp = floor($now / $duration) - 6;
$t = $timestamp * $duration;

// 建立 M3U8 內容
$m3u8 = "#EXTM3U\r\n";
$m3u8 .= "#EXT-X-VERSION:3\r\n";
$m3u8 .= "#EXT-X-TARGETDURATION:{$duration}\r\n";
$m3u8 .= "#EXT-X-MEDIA-SEQUENCE:{$timestamp}\r\n";

// 產生 5 個切片連結
for ($i = 0; $i < 5; $i++) {
    $m3u8 .= "#EXTINF:{$duration}.0,\r\n";
    $m3u8 .= "https://ntd-tgc.cdn.hinet.net{$id}/litv-pc/{$id}-avc1_6000000={$videoParam}-mp4a_134000_zho={$audioParam}-begin={$t}0000000-dur=40000000-seq={$timestamp}.ts\r\n";
    $timestamp++;
    $t += $duration;
}

// 輸出標頭
header('Content-Type: application/vnd.apple.mpegurl');
header('Content-Disposition: inline; filename="'.$id.'.m3u8"');
header('Cache-Control: no-cache');

echo $m3u8;
?>
