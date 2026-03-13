<?php
/**
 * CCTV 直播源自動抓取工具
 * 功能：每小時自動抓取央視新聞移動端直播位址並生成 M3U
 */

// 1. 設定頻道清單 (名稱 => 原始網頁網址)
$channels = [
    "CCTV4K超高清"   => "https://cctv.com",
    "CCTV1綜合"      => "https://cctv.com",
    "CCTV2財經"      => "https://cctv.com",
    "CCTV4中文國際"  => "https://cctv.com",
    "CCTV7國防軍事"  => "https://cctv.com",
    "CCTV9紀錄"      => "https://cctv.com",
    "CCTV10科教"     => "https://cctv.com",
    "CCTV12社會與法" => "https://cctv.com",
    "CCTV13新聞"     => "https://cctv.com",
    "CCTV17農業農村" => "https://cctv.com"
];

// 2. 抓取核心函數
function get_cctv_m3u8($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    // 模擬手機瀏覽器，避免被阻擋
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Mobile/15E148 Safari/604.1');
    
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return null;

    // 央視頁面會將直播資訊藏在 JS 變數中，通常包含 .m3u8 關鍵字
    // 使用正規表達式提取第一個匹配的 m3u8 連結
    if (preg_match('/["\'](https?:\/\/[^"\']+\.m3u8[^"\']*)["\']/', $html, $matches)) {
        // 處理反斜槓轉義並返回純網址
        return str_replace('\\/', '/', $matches[1]);
    }
    return null;
}

// 3. 處理請求
$m3u_output = "#EXTM3U x-tvg-url=\"http://51zmt.top\"" . PHP_EOL;

foreach ($channels as $name => $web_url) {
    $live_url = get_cctv_m3u8($web_url);
    if ($live_url) {
        $m3u_output .= "#EXTINF:-1 tvg-name=\"$name\" group-title=\"央視\", $name" . PHP_EOL;
        $m3u_output .= $live_url . PHP_EOL;
    }
}

// 4. 存檔 (供 Crontab 或定期任務使用)
file_put_contents("cctv_list.m3u", $m3u_output);

// 5. 如果是直接瀏覽此 PHP，則即時輸出 M3U 內容
header('Content-Type: audio/x-mpegurl');
header('Content-Disposition: attachment; filename="cctv.m3u"');
echo $m3u_output;
?>
