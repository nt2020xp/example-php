<?php
// 1. 設定目標 API (需透過 F12 監控找出 CCTV 實際的 JSON 接口網址)
$apiUrl = "api.cctvnews.cctv.com";

// 2. 初始化 cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.0 Mobile/15E148 Safari/604.1");
curl_setopt($ch, CURLOPT_REFERER, "https://m-live.cctvnews.cctv.com/");

$response = curl_exec($ch);
curl_close($ch);

// 3. 解析 JSON 資料
$data = json_decode($response, true);
if (isset($data['data']['hls_url'])) {
    $m3u8_url = $data['data']['hls_url'];
    echo "抓取成功！直播源網址：\n" . $m3u8_url;
} else {
    echo "無法解析直播網址，可能 API 結構已更動或需要驗證碼。";
}
?>
