<?php
$url = "api.cctvnews.cctv.com";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// 必須補足以下 Headers，否則伺服器會拒絕請求
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: https://m-live.cctvnews.cctv.com',
    'Referer: https://m-live.cctvnews.cctv.com/',
    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    'Accept: application/json, text/plain, */*',
    'Accept-Language: zh-CN,zh;q=0.9',
]);

$response = curl_exec($ch);
curl_close($ch);

// 如果回傳是空的，代表 IP 可能被暫時封鎖或需要 Cookie
echo $response; 
?>
