02.04 07:00
<?php
// 频道代理脚本
// 从GET参数中获取频道名称
$channel = isset($_GET['channel']) ? trim($_GET['channel']) : '';
if (empty($channel)) {
    header('HTTP/1.1 400 Bad Request');
    die('错误：请指定频道参数');
}
// 文档内容（这里应该是您提供的完整文档）
$documentContent = <<<DOC
# 这里放置您提供的完整文档内容
# 例如：
# 央视CCTV,#genre#
# CCTV 1 综合,http://127.0.0.1:43251/aHR0cDovLzQ1Ljg4LjE1MS42Njo5NTYwLzAxY2N0djEvaW5kZXgubTN1OD92LmNoYW5uZWxpZD0xMTg0JnYudHlwZT0xJmlwcz01MS43OS4xNy4zODoxOTk4NA==
# ... 其他频道
DOC;
// 解析文档，构建频道映射
$channels = [];
$lines = explode("\n", $documentContent);
foreach ($lines as $line) {
    $line = trim($line);
    // 跳过空行和分类行
    if (empty($line) || strpos($line, '#genre#') !== false) {
        continue;
    }
    // 解析频道行：频道名称,URL
    if (strpos($line, ',') !== false) {
        list($chanName, $url) = explode(',', $line, 2);
        $chanName = trim($chanName);
        $url = trim($url);
        if (!empty($chanName) && !empty($url)) {
            $channels[$chanName] = $url;
        }
    }
}
// 查找指定频道
if (!isset($channels[$channel])) {
    header('HTTP/1.1 404 Not Found');
    die('错误：未找到频道 "' . htmlspecialchars($channel) . '"');
}
$targetUrl = $channels[$channel];
// 设置适当的响应头
header('Content-Type: application/vnd.apple.mpegurl');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
// 使用cURL转发请求
$ch = curl_init();
// 设置cURL选项
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HEADER => false,
    CURLOPT_BUFFERSIZE => 8192,
    // 直接输出到浏览器
    CURLOPT_WRITEFUNCTION => function($curl, $data) {
        echo $data;
        return strlen($data);
    }
]);
// 执行请求
$result = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($result === false) {
    header('HTTP/1.1 502 Bad Gateway');
    die('代理错误：' . $error);
}
exit;
?>
<?php
// 增强版代理脚本，支持频道列表
$action = isset($_GET['action']) ? $_GET['action'] : 'play';
if ($action === 'list') {
    // 返回频道列表
    header('Content-Type: application/json');
    $channelList = [];
    foreach ($channels as $name => $url) {
        $channelList[] = [
            'name' => $name,
            'url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?channel=' . urlencode($name)
        ];
    }
    echo json_encode($channelList, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
// 原有的播放逻辑...
?>

