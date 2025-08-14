<?php
// 设置脚本执行时间和内存限制
set_time_limit(0);
ini_set('memory_limit', '512M');

// 目标CCTV纪录频道URL
$targetUrl = 'https://tv.cctv.com/live/cctvjilu';

// 检查请求的子路径（如果需要代理页面中的其他资源）
$resource = isset($_GET['resource']) ? $_GET['resource'] : '';
if (!empty($resource)) {
    $targetUrl = 'https://tv.cctv.com' . $resource; // 拼接资源URL
}

// 初始化cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 需要处理内容以重写URL
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟客户端User-Agent
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 忽略SSL验证（仅测试用）
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// 添加必要的HTTP头
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Referer: https://tv.cctv.com/live/cctvjilu',
    'Origin: https://tv.cctv.com',
]);

// 执行cURL请求
$content = curl_exec($ch);

// 检查错误
if (curl_errno($ch)) {
    http_response_code(500);
    exit('Error: ' . curl_error($ch));
}

// 获取响应头信息
$info = curl_getinfo($ch);
curl_close($ch);

// 设置适当的Content-Type
$contentType = $info['content_type'] ?? 'text/html; charset=utf-8';
header('Content-Type: ' . $contentType);
header('Cache-Control: no-cache');

// 如果是HTML内容，重写资源URL
if (stripos($contentType, 'text/html') !== false) {
    // 重写页面中的绝对路径为代理路径
    $content
