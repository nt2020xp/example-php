<?php
// 设置网页输出为纯文本格式
header("Content-Type: text/plain; charset=utf-8");

// 获取待检测的IP:端口列表（格式：IP:端口）
function getIpPortList() {
    $url = "http://yewengood.zone.id/fz.php";
    $content = file_get_contents($url);
   
    if ($content === false) {
        return ["error" => "无法获取IP:端口列表文件"];
    }
   
    $lines = explode("\n", trim($content));
    $ipPortList = [];
    foreach ($lines as $line) {
        $line = trim($line);
        // 仅保留符合 IP:端口 格式的记录（IP为IPv4，端口1-65535）
        if (!empty($line) && preg_match('/\b(?:\d{1,3}\.){3}\d{1,3}:\d{1,5}\b/', $line)) {
            $ipPortList[] = $line;
        }
    }
   
    if (empty($ipPortList)) {
        return ["error" => "IP:端口列表为空或格式错误"];
    }
   
    return $ipPortList;
}

// 检测单个IP:端口是否有效
function checkIpPortValid($ip, $port) {
    $testUrl = "http://{$ip}:{$port}/ywotttv.bj.chinamobile.com/PLTV/88888888/224/3221226550/1.m3u8";
   
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $testUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
   
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
   
    return ($httpCode === 200 && $response !== false && strlen(trim($response)) > 0);
}

// 随机抽取IP:端口检测，有效则直接返回
function findValidIpPort() {
    $ipPortList = getIpPortList();
    if (isset($ipPortList["error"])) {
        return $ipPortList; // 返回错误**
    }
   
    $total = count($ipPortList);
    echo "找到 {$total} 个IP:端口组合，开始随机检测...\n\n";
   
    $tested = []; // 记录已检测的IP:端口，避免重复检测
    $maxAttempts = min(50, $total); // 最多尝试50次（或列表总数）
   
    for ($i = 1; $i <= $maxAttempts; $i++) {
        // 从剩余未检测的列表中随机选一个
        $remaining = array_diff($ipPortList, $tested);
        if (empty($remaining)) {
            return ["error" => "所有IP:端口均已检测，无有效结果"];
        }
        
        $randomKey = array_rand($remaining);
        $currentIpPort = $remaining[$randomKey];
        list($ip, $port) = explode(':', $currentIpPort, 2);
        
        echo "随机检测第 {$i}/{$maxAttempts} 个：{$currentIpPort} ... ";
        if (checkIpPortValid($ip, $port)) {
            echo "有效\n\n";
            return [
                "ip" => $ip,
                "port" => $port,
                "ip_port" => $currentIpPort
            ];
        } else {
            echo "无效\n";
            $tested[] = $currentIpPort; // 标记为已检测，避免重复
        }
        usleep(300000); // 延迟0.3秒，避免请求过于频繁
    }
   
    return ["error" => "已尝试 {$maxAttempts} 次随机检测，未找到有效IP:端口"];
}

// 替换dz.txt中的IP:端口并输出结果
function replaceAndOutput() {
    // 1. 随机检测并获取有效IP:端口
    $valid = findValidIpPort();
    if (isset($valid["error"])) {
        return "错误：{$valid['error']}";
    }
    $newIpPort = $valid["ip_port"];
   
    // 2. 读取dz.txt内容
    $dzUrl = "http://yewengood.zone.id/dz.txt";
    echo "读取目标文件: {$dzUrl} ... \n";
    $dzContent = file_get_contents($dzUrl);
    if ($dzContent === false) {
        return "错误：无法读取dz.txt文件";
    }
   
    // 3. 替换所有IP:端口组合
    $ipPortPattern = '/\b(?:\d{1,3}\.){3}\d{1,3}:\d{1,5}\b/'; // 匹配IPv4:端口格式
    $replacedContent = preg_replace($ipPortPattern, $newIpPort, $dzContent);
   
    // 4. 输出替换结果
    $output = "=== 替换完成 ===" . PHP_EOL;
    $output .= "使用的有效IP:端口：{$newIpPort}" . PHP_EOL . PHP_EOL;
    $output .= "替换后的内容：" . PHP_EOL . PHP_EOL;
    $output .= $replacedContent;
   
    return $output;
}

// 执行主逻辑
echo replaceAndOutput();
?>
