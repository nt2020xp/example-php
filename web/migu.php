<?php
//本PHP首发于直播源论坛：https://bbs.livecodes.vip/

/**
 * 咪咕签名所需的盐值表
 * @return array
 */
function salt_table() {
    return [
    "b3bdac8bf67042f2965d92d9c1437053", "770fafdf5ba04d279a59ef1600baae98", "eee6aac1191e4e84afda27d1d6aa6b59",
    "b7fab2cdbe9c44a08c3e9eb2af93d4bb", "d2a987144aba48469e66bbbda63ba1c8", "deb34b761e704a31b65845c5f7b12886",
    "a8a868c3d290462496ebf041ff1f7bc9", "8324a239ed934d8bb88bdf031a09b9cf", "bf54dd9616fb4e6080bc985b0016921a",
    "9b8aceb24a674d00ad1e86c97bb40d00", "d3dadb4e177b40bd86d1ecd756bc1088", "c74abfa0ddf249118146f88aec97e77a",
    "d11c62e83baf4879bc089ec762df2e86", "8b7732373fa448ca89dd82cc630b92e9", "2d475c19231c4367b77d9d0d71acb0b7",
    "484d02e8aee54b2e8129c899f8a75eff", "bec179d5a34f482fb5bd4fe4cc4bbfc9", "3a9ee5d981544035bffa84ef662b6328",
    "ba5a6383024a4e198914ff64331d3519", "b40b343fe73c453399c50b06bff690c4", "e1900f69412c466fbfa4243d8cb57af5",
    "5e3bfc21c00846aa8dcc3e0cb4231c0d", "7ab8582a2efe41fc978041dbda1fbc53", "5f93c5bcd01b4d8ba870a5d524f65322",
    "efb0fa2362034750935dbe9c88e8a4d9", "5bf8a59b3b7846458d2928494f21f531", "32f25ab9dd2f4b3a9614cffaddaf2994",
    "186d0e0f31a64460bd99f09db5135a8a", "bc23a5de295e4efc8a58b0a1e331f87b", "da4523ed7163432596e6f583e5eee9bf",
    "6bba2c3e7a12401494638b8fcc3ccb32", "62c0f04c3a914ab0a237c66f00ac1c87", "3d58f0ffcfa9479ba627b56a1f966cde",
    "95f54cd474ed418da264fdc62d3f0eca", "032f69bc6bbb4183a0ca6860ff7a5b80", "2678ca342220416e9831bf21028bb3b4",
    "904aa38a9d584ae998db226b66f9150a", "534718e3ff1745979416bfd1d98860e2", "5b4fd6946f694ff79617fcb4f0d53913",
    "454a302947ef4635a14234946e850f20", "320104ad02ff487b8c83761c71b344ee", "acdcc42ae086442f99d2152d665b2cac",
    "e888d7b620854e45abbe85c1d7754921", "6298f3fec8eb4273ab6ac52c6b763dcb", "898d52af841b4a54b0cbcbc49faa1d47",
    "6678bd9767e3468eb85522435eacbce3", "53c5ef2f866a49eaa74825e70d8db3ac", "59d1cc6b9265407f954f48e5d8f1ac69",
    "f12d498fc4704b85b4759277c1f80212", "75f62d8f88134d468ffc5d15a0cf57cc", "69b949623bb047c9bdb38b6636a1daad",
    "ade7a1bf7f3d4da3bd77ee0427cf91c9", "35d54a787ef54bb3bdd1eb3fe806fa8f", "7af240a3b0d74e5190f684e5cf14b5ea",
    "0f4b448f661642aeaf20fc50ae18e132", "78a9543e97224952ada17316ed1f1e4b", "86b2007310ef4bdf9a95f84cacce79d3",
    "009da9f69d1b477eb4c8bacd479fb437", "d18bbc0e9d6a458b8181d10c64f69bf9", "2e69fb35e7e54fe3b50ae42f2c33a355",
    "2ba256cecade41ba8b0b38fb82485ba4", "a472331c09b5486fbe7642afe3d5e564", "00376402f9c642029350f4299877b8a7",
    "682c4f83c66540c0b16e84252f00c959", "772a60fe9c494963992ad4a3b7e42310", "f155a35565fa47f3911fa4610aaccfc0",
    "ef2519f7334e437d97f9245073a7be6b", "2a7bf4efd63c4ebea0d5052797f79e93", "1d7c3a46c1564500946d88c581ffde03",
    "3d4b0be7872b45ee984bac6b748b91fe", "0be9723300b64e0d8602fb326e6d8dfe", "8b36b81555274b29912e188bb90f9f21",
    "de652c1802f045bfa2baa8c9ac37a8d1", "43fcd2564c094bc3b992995022a7dedb", "5cd89e5b39c34c819b9a6d5b9303dc80",
    "0a6eebc249a748dbaf44e77f83979ec4", "35c290379d204b3d83cbb3985fc007cb", "0c23c3e73e1342bdb04edccd67fdb26b",
    "730a1a2fd81643cdad26c22dc6a6220f", "a0ab6d06ddfd4bf795292f8c2dc7077a", "f2f53bd144e14f6e92f996b9ca40765c",
    "6edd9d658900444ab93770aa3cb21f70", "687de5ae53944922835d711a5d137ace", "7aeb21c41c71461cb5aeb9c53bec429c",
    "20fd4caf8fce4210928374bea1cadde9", "2e63e91f276840dfa54055234ef178f6", "ac5cacc2d3164078a0a2a09ff45efb57",
    "9e11aaa8db234e37a91e340757a939c1", "5ce23be6515345b1b758af58511d7ba5", "7944802dfc734bb2857cd36f99dbde8e",
    "2ae4114c91e34591949261d5c4408d4f", "7a3134777d9f45ffbb69e0d1d508c588", "bd1ff2c60a384c0bb9dc48dd3fa78372",
    "6512fd685dbf4052942561a659af42f4", "9a0f1dd156e44959b5598bf7f9d6aa33", "7a2414c185334d76837d38f44709681e",
    "2ff0ef10c83b403baa7d506a6963730e", "71446601613d49f28e13619a0f7c0d81", "aa47ca4afbc148038f0f7ec609196cf3",
    "de955dd1a8aa44ca9076c2af9def94da",
    ];
}

/**
 * 获取咪咕缓存目录路径，如果不存在则创建
 * @return string
 */
function migu_cache_dir(): string
{
    $dir = __DIR__ . '/migucache';
    // 使用 @ 符号抑制错误，因为权限问题可能导致创建失败
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

/**
 * 根据键名生成缓存文件的完整路径
 * @param string $key 缓存键名 (通常是contId)
 * @return string
 */
function cache_path($key) {
    return migu_cache_dir()."/migu_cache_".md5($key).".json";
}

/**
 * 从缓存中获取数据
 * @param string $key 缓存键名
 * @return array [url, hit_status]
 */
function get_migu_cache($key) {
    $p = cache_path($key);
    if (!is_file($p)) return [null,false];
    // 读取并解析 JSON，使用 @ 符号抑制文件读取错误
    $d = json_decode(@file_get_contents($p), true);
    if (!$d || !isset($d['time']) || !isset($d['ttl'])) return [null,false];
    
    // 检查缓存是否过期
    if (time() - intval($d['time']) > intval($d['ttl'])) { 
        @unlink($p); // 过期则删除
        return [null,false]; 
    }
    return [$d['url'], true];
}

/**
 * 设置缓存数据
 * @param string $key 缓存键名
 * @param string $url 播放链接
 * @param int $ttl_seconds 缓存过期时间（秒）
 */
function set_migu_cache($key, $url, $ttl_seconds) {
    $p = cache_path($key);
    // 使用 JSON_UNESCAPED_SLASHES 避免 URL 中的斜杠被转义
    // 使用 @ 符号抑制文件写入错误
    @file_put_contents($p, json_encode(['url'=>$url,'time'=>time(),'ttl'=>$ttl_seconds], JSON_UNESCAPED_SLASHES));
}

/**
 * 生成请求所需的 salt 和 sign
 * @param string $md5string
 * @return array [$salt, $sign]
 */
function url_sign($md5string) {
    // 生成 8 位随机盐值
    $salt = strval(random_int(10000000, 99999999));
    $saltInt = intval(substr($salt, 6)); // 取后两位数字
    $idx = $saltInt % 100; // 索引
    $table = salt_table();
    
    // 签名核心逻辑
    $text = $md5string . $table[$idx] . "migu" . substr($salt, 0, 4);
    $sign = md5($text);
    return [$salt, $sign];
}

/**
 * 根据 contId 获取签名所需的 tm, salt, sign
 * @param string $contId
 * @return array [$tm, [$salt, $sign]]
 */
function get_sign_config($contId) {
    $appVersion = "2600000900";
    // 毫秒级时间戳
    $tm = (string)intval(microtime(true) * 1000); 
    $md5string = md5($tm . $contId . substr($appVersion, 0, 8));
    return [$tm, url_sign($md5string)];
}

/**
 * 发送 GET 请求
 * @param string $url 请求 URL
 * @param array $headers 请求头
 * @return string|null 响应体或 null (失败时)
 */
function send_get_request($url, $headers) {
    // 检查 cURL 扩展是否可用
    if (!extension_loaded('curl')) {
        // 在生产环境中应返回错误日志，此处简化处理
        error_log("cURL extension is not loaded.");
        return null;
    }

    $ch = curl_init($url);
    $h = [];
    foreach ($headers as $k=>$v) $h[] = $k.": ".$v;
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
        CURLOPT_HTTPHEADER => $h,
        // 允许 https 请求
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10, // 设置超时时间
    ]);
    
    $body = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);
    
    if ($err) return null;
    return $body;
}

/**
 * 咪咕 URL 加密计算 (ddCalcu)
 * @param string $str 原始 URL
 * @return string 加密后的 URL
 */
function migu_encrypted_url($str) {
    if (trim($str) === "") {
        return "";
    }

    $parts = parse_url($str);
    if ($parts === false || !isset($parts['query'])) {
        return "";
    }

    // 解析查询参数
    parse_str($parts['query'], $q);

    $S = isset($q['puData']) ? $q['puData'] : "";
    $U = isset($q['userid']) ? $q['userid'] : "";
    $T = isset($q['timestamp']) ? $q['timestamp'] : "";
    $P = isset($q['ProgramID']) ? $q['ProgramID'] : "";
    $C = isset($q['Channel_ID']) ? $q['Channel_ID'] : "";
    $V = isset($q['playurlVersion']) ? $q['playurlVersion'] : "";

    // preg_split('//u', ...) 用于将字符串按 UTF-8 字符分割成数组
    $sRunes = preg_split('//u', $S, -1, PREG_SPLIT_NO_EMPTY);
    $N = count($sRunes);
    $half = (int)(($N + 1) / 2);

    $sb = "";

    for ($i = 0; $i < $half; $i++) {
        // 如果 S 长度为奇数，将中间字符直接追加
        if ($N % 2 == 1 && $i == $half - 1) {
            $sb .= $sRunes[$i];
            break;
        }

        // 字符交叉组合逻辑
        $sb .= $sRunes[$N - 1 - $i]; // S 的末尾字符
        $sb .= $sRunes[$i];         // S 的开头字符

        // 混入其他参数字符的逻辑
        switch ($i) {
            case 1:
                $uRunes = preg_split('//u', $U, -1, PREG_SPLIT_NO_EMPTY);
                if (count($uRunes) > 2) {
                    $sb .= $uRunes[2];
                } else {
                    $vRunes = preg_split('//u', $V, -1, PREG_SPLIT_NO_EMPTY);
                    if (count($vRunes) > 0) {
                        // mb_strtolower 用于处理多字节字符的转小写
                        $sb .= mb_strtolower($vRunes[count($vRunes) - 1], 'UTF-8');
                    }
                }
                break;
            case 2:
                $tRunes = preg_split('//u', $T, -1, PREG_SPLIT_NO_EMPTY);
                if (count($tRunes) > 6) {
                    $sb .= $tRunes[6];
                } else {
                    $sb .= $sRunes[$i];
                }
                break;
            case 3:
                $pRunes = preg_split('//u', $P, -1, PREG_SPLIT_NO_EMPTY);
                if (count($pRunes) > 2) {
                    $sb .= $pRunes[2];
                } else {
                    $sb .= $sRunes[$i];
                }
                break;
            case 4:
                $cRunes = preg_split('//u', $C, -1, PREG_SPLIT_NO_EMPTY);
                if (count($cRunes) >= 4) {
                    $sb .= $cRunes[count($cRunes) - 4];
                } else {
                    $sb .= $sRunes[$i];
                }
                break;
        }
    }

    $base = $str;
    if (($idx = strpos($str, "?")) !== false) {
        $base = substr($str, 0, $idx);
    }

    $dd = $sb;
    // 将计算结果 ddCalcu 追加到原始 URL
    $result = sprintf("%s?%s&ddCalcu=%s", $base, $parts['query'], $dd);
    return $result;
}


/**
 * 主请求处理函数：获取播放链接
 * @param string $id 内容 ID (contId)
 * @return string|null 播放链接或 null (失败时)
 */
function handle_migu_main_request($id) {
    // 1. 尝试从缓存中获取
    [$cached, $hit] = get_migu_cache($id);
    if ($hit) return $cached;

    // 2. 生成签名配置
    [$tm, $saltSign] = get_sign_config($id);
    $salt = $saltSign[0];
    $sign = $saltSign[1];

    // 3. 构造请求 URL
    $url = sprintf(
        "https://play.miguvideo.com/playurl/v1/play/playurl?contId=%s&dolby=true&isMultiView=true&xh265=true&os=13&ott=false&rateType=3&salt=%s&sign=%s&timestamp=%s&ua=oneplus-12&vr=true",
        $id, $salt, $sign, $tm
    );

    // 4. 构造请求头
    $headers = [
        "Host" => "play.miguvideo.com",
        "appId" => "miguvideo",
        "terminalId" => "android",
        "User-Agent" => "Dalvik/2.1.0+(Linux;+U;+Android+13;+oneplus-13+Build/TP1A.220624.014)",
        "MG-BH" => "true",
        "appVersionName" => "6.0.9.00",
        "appVersion" => "2600000900",
        "Phone-Info" => "oneplus-13|13",
        "X-UP-CLIENT-CHANNEL-ID" => "2600000900-99000-200300140100004",
        "APP-VERSION-CODE" => "25000653",
        "Accept" => "*/*",
        "Connection" => "keep-alive",
    ];

    // 5. 发送请求
    $body = send_get_request($url, $headers);
    if ($body === null) return null;

    // 6. 解析响应
    $json = json_decode($body, true);
    $rawUrl = "";
    if (is_array($json) && isset($json["body"]["urlInfo"]["url"])) {
        $rawUrl = (string)$json["body"]["urlInfo"]["url"];
    } else {
        // 如果 JSON 解析失败或结构不正确，返回 null
        return null;
    }
    
    // 7. 进行 URL 加密（ddCalcu）
    $ottUrl = migu_encrypted_url($rawUrl);
    if (trim($ottUrl) === "") return null;

    // 8. 写入缓存，过期时间 1800 秒（30 分钟）
    set_migu_cache($id, $ottUrl, 1800);
    return $ottUrl;
}

// ===================================
// 脚本执行入口
// ===================================

// 从 GET 参数获取 contId，如果未提供，使用默认值
$id = isset($_GET['id']) ? (string)$_GET['id'] : "608807420"; 
$res = handle_migu_main_request($id);

// 检查是否成功获取到 URL
if (!empty($res)) {
    // 成功获取，进行 HTTP 302/307 重定向到最终播放链接
    header('Location: ' . $res);
    exit; // 重定向后立即停止脚本执行，节省资源
} else {
    // 失败，输出错误信息
    http_response_code(500); // 设置 HTTP 状态码为 500 Internal Server Error
    header('Content-Type: application/json; charset=utf-8'); // 指定返回 JSON 格式
    
    // 返回一个包含错误信息的 JSON 响应
    echo json_encode([
        'code' => 500,
        'message' => 'Failed to get playback URL. Check contId, cURL extension, and server logs.',
        'contId' => $id,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}
