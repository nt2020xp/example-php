<?php
// 目标API地址
$apiUrl = 'https://capi.yangshipin.cn/api/oms/pc/page/PG00000004.json';

// 使用cURL获取数据
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_REFERER, 'https://tv.cctv.com/');

$json = curl_exec($ch);
curl_close($ch);

if ($json === false) {
    die('无法获取数据');
}

// 解析JSON数据
$data = json_decode($json, true);

// 检查数据结构并提取信息
if (isset($data['data']['feedModuleList'][0]['dataTvChannelList'])) {
    $channels = $data['data']['feedModuleList'][0]['dataTvChannelList'];
    
    foreach ($channels as $channel) {
        if (isset($channel['channelName'], $channel['pid'], $channel['streamId'])) {
            echo $channel['channelName'] . ', ***/cnlid=' . $channel['streamId'] . '&livepid=' . $channel['pid'] . "\n";
        }
    }
} else {
    echo '未找到频道数据';
}
?>
