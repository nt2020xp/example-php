<?php
// 央视频 API 生成 UID（示例，具体参数以官方文档为准）
$api_url = "https://api.cctv.cn/v1/uid/generate";
$api_key = "YOUR_API_KEY"; // 替换为你的开发者密钥

$data = [
    "app_id" => "YOUR_APP_ID", // 你的应用ID
    "timestamp" => time(),
    "nonce" => bin2hex(random_bytes(8)), // 随机数
];

// 计算签名（假设使用 HMAC-SHA256）
$signature = hash_hmac('sha256', json_encode($data), $api_key);
$data['signature'] = $signature;

// 发送请求
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
curl_close($ch);

// 解析返回的 UID
$result = json_decode($response, true);
if ($result && $result['code'] == 200) {
    $valid_uid = $result['data']['uid'];
    echo "官方生成的 UID: " . $valid_uid;
} else {
    echo "UID 获取失败: " . ($result['message'] ?? '未知错误');
}
?>
