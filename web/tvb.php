<?php

$url = 'https://www.faintv.com/channel/17767';
$output_file = 'tvb_stream_source.txt'; // 儲存直播源的檔案

// --- 1. 使用 cURL 獲取網頁內容 ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// 模擬瀏覽器行為，避免被阻擋
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟隨重定向
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 如果網站是HTTPS，可能需要禁用SSL檢查 (生產環境不建議)

$html = curl_exec($ch);

if (curl_errno($ch)) {
    $error = 'cURL 錯誤: ' . curl_error($ch);
    file_put_contents($output_file, date('Y-m-d H:i:s') . ' - ' . $error . "\n", FILE_APPEND);
    curl_close($ch);
    exit;
}

curl_close($ch);

// --- 2. 解析 HTML 內容提取直播源 ---
$live_stream_url = '';

// *** 這裡需要根據網頁實際結構來修改！***
// 直播源通常是 m3u8 格式，你可以嘗試使用正則表達式來查找。
// 範例：尋找所有以 http 或 https 開頭，以 .m3u8 結尾的連結
if (preg_match('/(https?:\/\/[^\s\'"]+\.m3u8)/i', $html, $matches)) {
    $live_stream_url = $matches[1];
} 
// 或者，如果直播源是藏在某個特定的 HTML 標籤或 JavaScript 變量中，
// 您需要使用 DOMDocument 或更精確的正則表達式來提取。
// **********************************

// --- 3. 儲存結果 ---
if (!empty($live_stream_url)) {
    $content_to_save = date('Y-m-d H:i:s') . ' - 成功抓取: ' . $live_stream_url . "\n";
} else {
    $content_to_save = date('Y-m-d H:i:s') . ' - 失敗: 未找到直播源。' . "\n";
}

// 將結果寫入檔案，使用 FILE_APPEND 模式附加到文件末尾
file_put_contents($output_file, $content_to_save, FILE_APPEND);

echo "抓取完成，請檢查 $output_file 檔案。\n";

?>
