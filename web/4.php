<?php
error_reporting(0);
header('Content-Type: text/json;charset=UTF-8');

// 频道列表
$ch4g = array(
    "4gtv-4gtv039" => "八大综艺台",
    "4gtv-live089" => "三立财经新闻",
    "litv-ftv13" => "民視新聞台",
    "litv-longturn14" => "寰宇新聞台",
    "4gtv-4gtv052" => "華視新聞資訊台",
    "4gtv-4gtv012" => "空中英語教室",
    "litv-ftv07" => "民視旅遊台",
    "litv-ftv15" => "i-Fun動漫台",
    "4gtv-live206" => "幸福空間居家台",
    "4gtv-4gtv070" => "愛爾達娛樂台",
    "litv-longturn17" => "亞洲旅遊台",
    "4gtv-4gtv025" => "MTV Live HD",
    "litv-longturn15" => "寰宇新聞台灣台",
    "4gtv-4gtv001" => "民視台灣台",
    "4gtv-4gtv074" => "中視新聞台",
    "4gtv-4gtv011" => "影迷數位電影台",
    "4gtv-4gtv047" => "靖天日本台",
    "litv-longturn11" => "龍華日韓台",
    "litv-longturn12" => "龍華偶像台",
    "4gtv-4gtv042" => "公視戲劇",
    "litv-ftv12" => "i-Fun動漫台3",
    "4gtv-4gtv002" => "民視無線台",
    "4gtv-4gtv027" => "CI 罪案偵查頻道",
    "4gtv-4gtv013" => "CNEX紀實頻道",
    "litv-longturn03" => "龍華電影台",
    "4gtv-4gtv004" => "民視綜藝台",
    "litv-longturn20" => "ELTV英語學習台",
    "litv-longturn01" => "龍華卡通台",
    "4gtv-4gtv040" => "中視無線台",
    "litv-longturn02" => "Baby First",
    "4gtv-4gtv003" => "民視第一台",
    "4gtv-4gtv007" => "大愛電視台",
    "4gtv-4gtv076" => "SMART 知識頻道",
    "4gtv-4gtv030" => "CNBC",
    "litv-ftv10" => "半島電視台"
);

$ch4g2 = array(
    "31" => "民視新聞台",
    "234" => "東森新聞台",
    "36" => "寰宇新聞台",
    "129" => "TVBS新聞",
    "33" => "中視新聞",
    "34" => "華視新聞",
    "268" => "鏡電視新聞台",
    "30" => "中天新聞台",
    "229" => "三立新聞iNEWS",
    "273" => "原住民族電視台",
    "85" => "半島國際新聞台",
    "86" => "VOA美國之音",
    "226" => "DW德國之聲",
    "223" => "新唐人亞太台",
    "211" => "東森財經新聞台",
    "235" => "CNBC Asia 財經台",
    "170" => "國會頻道1",
    "171" => "國會頻道2",
    "113" => "豬哥亮歌廳秀",
    "236" => "金光布袋戲",
    "3" => "民視",
    "4" => "中視",
    "6" => "華視",
    "209" => "大愛電視",
    "272" => "電影免費看",
    "270" => "戲劇免費看",
    "269" => "兒童卡通台",
    "204" => "精選動漫台",
    "201" => "經典電影台",
    "244" => "華語戲劇台",
    "245" => "華語綜藝台",
    "202" => "經典卡通台",
    "219" => "Nick Jr. 兒童頻道",
    "274" => "fun探索娛樂台",
    "107" => "客家電視台",
    "250" => "亞洲旅遊台",
    "251" => "中天美食旅遊",
    "249" => "滾動力rollor",
    "181" => "TechStorm",
    "1" => "民視第一台",
    "2" => "民視台灣台",
    "16" => "民視綜藝台",
    "24" => "民視影劇台",
    "139" => "Love Nature",
    "25" => "采昌影劇台",
    "21" => "靖天綜合台",
    "42" => "靖天映畫",
    "7" => "公視戲劇",
    "83" => "靖天育樂台",
    "185" => "尼克兒童頻道",
    "40" => "影迷數位電影台",
    "58" => "智林體育台",
    "22" => "靖天日本台",
    "82" => "靖天電影台",
    "123" => "中視菁采台",
    "169" => "三立綜合台",
    "214" => "History 歷史頻道",
    "217" => "Lifetime 娛樂頻道",
    "52" => "博斯網球台",
    "48" => "博斯高球台",
    "180" => "ROCK Action",
    "179" => "GINX Esports TV",
    "50" => "博斯運動一台",
    "51" => "博斯無限台",
    "57" => "TRACE Sport Stars",
    "69" => "時尚運動X",
    "160" => "車迷TV",
    "231" => "MOMO親子台",
    "11" => "達文西頻道",
    "15" => "靖天卡通台",
    "59" => "靖洋卡通Nice Bingo",
    "60" => "i-Fun動漫台",
    "106" => "ELTV生活英語台",
    "121" => "龍華電影台",
    "177" => "CATCHPLAY電影台",
    "200" => "CATCHPLAY Beyond",
    "176" => "My Cinema Europe HD 我的歐洲電影",
    "23" => "龍華戲劇台",
    "28" => "龍華日韓台",
    "172" => "八大精彩台",
    "116" => "靖天戲劇台",
    "118" => "靖洋戲劇台",
    "225" => "CinemaWorld",
    "39" => "amc最愛電影",
    "212" => "影迷數位紀實台",
    "38" => "視納華仁紀實頻道",
    "178" => "TV5MONDE STYLE HD 生活時尚",
    "175" => "LUXE TV Channel",
    "61" => "民視旅遊台",
    "168" => "幸福空間居家台",
    "252" => "Global Trekker",
    "189" => "ARIRANG阿里郎頻道",
    "237" => "愛爾達生活旅遊台"
);

// 获取频道参数
$channel = $_GET['channel'] ?? '';
$headers = [
    "User-Agent: okhttp/3.12.11",
    "Referer: https://www.4gtv.tv/",
    "Origin: https://www.4gtv.tv"
];

if (empty($channel)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Channel parameter is required']));
}

// 处理频道请求
if (array_key_exists($channel, $ch4g2)) {
    // 第一种频道处理方式
    $url = "https://api2.4gtv.tv/Channel/GetChannel/" . $channel;
    $data = curlRequest($url, $headers);
    
    if (strpos($data, 'Error:') === 0) {
        http_response_code(500);
        exit(json_encode(['error' => substr($data, 7)]));
    }
    
    $obj = json_decode($data, true);
    if (!$obj || !isset($obj["Data"])) {
        http_response_code(404);
        exit(json_encode(['error' => 'Channel data not found']));
    }
    
    $cno = $obj["Data"]["fnID"];
    $cid = $obj["Data"]["fs4GTV_ID"];

    $jarray = array(
        "fnCHANNEL_ID" => $cno,
        "fsASSET_ID" => $cid,
        "fsDEVICE_TYPE" => "mobile",
        "clsIDENTITY_VALIDATE_ARUS" => array("fsVALUE" => "")
    );
    
    $abc = json_encode($jarray);
    $key = "ilyB29ZdruuQjC45JhBBR7o2Z8WJ26Vg";
    $iv = "JUMxvVMmszqUTeKn";
    $enc = openssl_encrypt($abc, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
    $enc = base64_encode($enc);

    $postData = "value=" . urlencode($enc);
    $resp = curlRequest("https://api2.4gtv.tv/Channel/GetChannelUrl3", $headers, $postData);
    
    if (strpos($resp, 'Error:') === 0) {
        http_response_code(500);
        exit(json_encode(['error' => substr($resp, 7)]));
    }
    
    $resp = json_decode($resp, true);
    if (!$resp || !isset($resp["Data"])) {
        http_response_code(404);
        exit(json_encode(['error' => 'Stream URL not found']));
    }
    
    $resp = openssl_decrypt(base64_decode($resp["Data"]), "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
    $playlist = json_decode($resp, true)["flstURLs"][0] ?? '';
    
    if (empty($playlist)) {
        http_response_code(404);
        exit(json_encode(['error' => 'No playable stream found']));
    }
    
    header("Location: " . $playlist);
    exit;
} elseif (array_key_exists($channel, $ch4g)) {
    // 第二种频道处理方式
    $url = "https://app.4gtv.tv/Data/HiNet/GetURL.ashx?Type=LIVE&Content=" . $channel;
    $response = curlRequest($url, $headers);
    
    if (strpos($response, 'Error:') === 0) {
        http_response_code(500);
        exit(json_encode(['error' => substr($response, 7)]));
    }
    
    $jsonStr = findString($response, "{", "}");
    $data = json_decode($jsonStr, true);
    
    if (!$data || !isset($data['VideoURL'])) {
        http_response_code(404);
        exit(json_encode(['error' => 'Stream URL not found']));
    }
    
    $vUrl = $data['VideoURL'];
    $hexkey = "VxzAfiseH0AbLShkQOPwdsssw5KyLeuv";
    $hexiv = substr($vUrl, 0, 16);
    $streamurl = openssl_decrypt(base64_decode(substr($vUrl, 16)), "AES-256-CBC", $hexkey, 1, $hexiv);
    
    if (empty($streamurl)) {
        http_response_code(404);
        exit(json_encode(['error' => 'Failed to decrypt stream URL']));
    }
    
    header("Location: " . $streamurl);
    exit;
} else {
    http_response_code(404);
    exit(json_encode(['error' => 'Channel not found']));
}

// 通用cURL请求函数
function curlRequest($url, $headers = [], $postData = null) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_ENCODING => '',
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = "Error: " . curl_error($ch);
        curl_close($ch);
        return $error;
    }
    
    curl_close($ch);
    return $response;
}

// 查找字符串函数
function findString($str, $start, $end) {
    $from_pos = strpos($str, $start);
    if ($from_pos === false) return '';
    
    $end_pos = strpos($str, $end, $from_pos);
    if ($end_pos === false) return '';
    
    return substr($str, $from_pos, ($end_pos - $from_pos + 1));
}
