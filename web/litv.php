<?php
/*
 * LiTV
 * 版本：v1.0
 * 作者：Passwd Word
 * 最后修改：2026-01-29
 * 功能说明：
 *   - 访问 `http://yourserver/live_litv.php?token=xxxx` 返回完整 M3U 列表
 *   - 访问 `http://yourserver/live_litv.php?token=xxxx&id=频道ID` 使用 API 获取指定频道的 M3U8 播放地址
 */

header('Content-Type: text/plain; charset=utf-8',true,200);
$SECRET_TOKEN = 'judy'; // 替换为你的实际token

// 检查token是否有效
if (!isset($_GET['token'])) {
    http_response_code(403);
    echo "Error: Access denied. Token is required.";
    exit;
}

if ($_GET['token'] !== $SECRET_TOKEN) {
    http_response_code(403);
    echo "Error: Invalid token.";
    exit;
}

// LiTV API 配置
$API_URL = 'https://www.litv.tv/api/get-urls';
$COOKIES = [];//<===自行填入
$PUID = '';//<===自行填入

// 频道映射表
// 格式: '频道ID' => ['tvg-id', '频道名称', '台标URL', '分组名称']
$channels = [
    '4gtv-4gtv001' => ['民視台灣台', '民視台灣台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV3.png','綜合其他'],
    '4gtv-4gtv002' => ['民視', '民視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV.png','綜合其他'],
    '4gtv-4gtv003' => ['民視第一台', '民視第一台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV2.png','綜合其他'],
    '4gtv-4gtv004' => ['民視綜藝台', '民視綜藝', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV6.png','音樂綜藝'],
    '4gtv-4gtv006' => ['豬哥亮歌廳秀', '豬哥亮歌廳秀', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV7.png','音樂綜藝'],
    '4gtv-4gtv009' => ['中天新聞台', '中天新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTI2.png','新聞財經'],
    '4gtv-4gtv010' => ['非凡新聞台', '非凡新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Unique1.png','新聞財經'],
    '4gtv-4gtv011' => ['影迷數位電影台', '影迷數位電影台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FANS1.png','電影戲劇'],
    '4gtv-4gtv013' => ['視納華仁紀實頻道', '視納華仁紀實頻道', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/cnex.png','紀實探索'],
    '4gtv-4gtv014' => ['時尚運動X', '時尚運動X', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/ssydX.png','體育競技'],
    '4gtv-4gtv016' => ['韓國娛樂台 KMTV', '韓國娛樂台 KMTV', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/hanguoyl.png','綜合其他'],
    '4gtv-4gtv017' => ['amc電影台', 'amc 電影台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/AMCMovies.png','電影戲劇'],
    '4gtv-4gtv018' => ['達文西頻道', '達文西頻道', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/DaVinci.png','兒童卡通'],
    '4gtv-4gtv034' => ['八大精彩台', '八大精彩台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GTV5.png','音樂綜藝'],
    '4gtv-4gtv039' => ['八大綜藝台', '八大綜藝台', 'https://4gtvimg2.4gtv.tv/4gtv-Image/Channel/mobile/logo_4gtv_4gtv-4gtv039_mobile.png','音樂綜藝'],
    '4gtv-4gtv040' => ['中視', '中視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTV.png','綜合其他'],
    '4gtv-4gtv041' => ['華視', '華視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTS.png','綜合其他'],
    '4gtv-4gtv042' => ['公視戲劇', '公視戲劇', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/PTS3.png','電影戲劇'],
    '4gtv-4gtv043' => ['客家電視台', '客家電視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Hakka.png','綜合其他'],
    '4gtv-4gtv044' => ['靖天卡通台', '靖天卡通台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV7.png','兒童卡通'],
    '4gtv-4gtv045' => ['靖洋戲劇台', '靖洋戲劇台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/jy2.png','電影戲劇'],
    '4gtv-4gtv046' => ['靖天綜合台', '靖天綜合台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV1.png','綜合其他'],
    '4gtv-4gtv047' => ['靖天日本台', '靖天日本台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV6.png','綜合其他'],
    '4gtv-4gtv048' => ['非凡商業台', '非凡商業台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Unique2.png','新聞財經'],
    '4gtv-4gtv049' => ['采昌影劇台', '采昌影劇', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/caichang.png','電影戲劇'],
    '4gtv-4gtv051' => ['台視新聞', '台視新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TTV2.png','新聞財經'],
    '4gtv-4gtv052' => ['華視新聞', '華視新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTS1.png','新聞財經'],
    '4gtv-4gtv053' => ['GINX Esports TV', 'GINX Esports TV', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GINXesport.png','體育競技'],
    '4gtv-4gtv054' => ['NICETV靖天歡樂台', '靖天歡樂', 'https://epg.pw/media/images/epg/2024/06/12/20240612111031068542_59.png','音樂綜藝'],
    '4gtv-4gtv055' => ['靖天映畫', '靖天映畫', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV3.png','電影戲劇'],
    '4gtv-4gtv056' => ['台視財經', '台視財經', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TTV3.png','新聞財經'],
    '4gtv-4gtv057' => ['靖洋卡通台Nice Bingo', '靖洋卡通台Nice Bingo', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/jy1.png','兒童卡通'],
    '4gtv-4gtv058' => ['靖天戲劇台', '靖天戲劇台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV8.png','電影戲劇'],
    '4gtv-4gtv059' => ['CLASSICA 古典樂', 'CLASSICA 古典樂', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/classical.png','音樂綜藝'],
    '4gtv-4gtv061' => ['靖天電影台', '靖天電影台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV9.png','電影戲劇'],
    '4gtv-4gtv062' => ['靖天育樂台', '靖天育樂台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV5.png','綜合其他'],
    '4gtv-4gtv063' => ['KLT-靖天國際台', 'KLT-靖天國際台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV10.png','綜合其他'],
    '4gtv-4gtv065' => ['靖天資訊台', '靖天資訊台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoldenTV2.png','綜合其他'],
    '4gtv-4gtv066' => ['台視', '台視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TTV.png','綜合其他'],
    '4gtv-4gtv070' => ['愛爾達娛樂台', 'ELTA娛樂', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/ELTA3.png','音樂綜藝'],
    '4gtv-4gtv074' => ['中視新聞', '中視新聞', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTV1.png','新聞財經'],
    '4gtv-4gtv075' => ['鏡電視新聞台', '鏡電視新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Mnews.png','新聞財經'],
    '4gtv-4gtv076' => ['亞洲旅遊台', '亞洲旅遊', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Asiatravel.png','生活旅遊'],
    '4gtv-4gtv077' => ['TRACESPORTSTARS', 'TRACE Sport Stars', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TraceSport.png','體育競技'],
    '4gtv-4gtv079' => ['ARIRANG阿里郎頻道', 'Arirang TV', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/ArirangTV.png','綜合其他'],
    '4gtv-4gtv080' => ['中視經典台', '中視經典台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTV2.png','電影戲劇'],
    '4gtv-4gtv082' => ['TRACE Urban', 'TRACE Urban', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TraceUrban.png','音樂綜藝'],
    '4gtv-4gtv083' => ['Mezzo Live HD', 'Mezzo Live HD', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/mezzolive.png','音樂綜藝'],
    '4gtv-4gtv084' => ['國會頻道1台', '國會頻道1台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/guohui1.png','綜合其他'],
    '4gtv-4gtv085' => ['國會頻道2台', '國會頻道2台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/guohui2.png','綜合其他'],
    '4gtv-4gtv101' => ['智林體育台', '智林體育台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/TSL.png','體育競技'],
    '4gtv-4gtv102' => ['東森購物1台', '東森購物1台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/EBC11.png','綜合其他'],
    '4gtv-4gtv103' => ['東森購物2台', '東森購物2台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/EBC11.png','綜合其他'],
    '4gtv-4gtv104' => ['第1商業', '第1商業', 'https://p-cdnstatic.svc.litv.tv/pics/logo_litv_4gtv-4gtv104_tv.png','新聞財經'],
    '4gtv-4gtv109' => ['中天亞洲台', '中天亞洲台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/CTI4.png','綜合其他'],
    '4gtv-4gtv110' => ['Pet Club TV', 'Pet Club TV', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/PetClubTV.png','生活旅遊'],
    '4gtv-4gtv152' => ['東森新聞台', '東森新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/EBC6.png','新聞財經'],
    '4gtv-4gtv153' => ['東森財經新聞台', '東森財經新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/EBC7.png','新聞財經'],
    '4gtv-4gtv155' => ['民視', '民視', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV.png','綜合其他'],
    '4gtv-4gtv156' => ['寰宇新聞台灣台', '寰宇新聞台灣台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Global3.png','新聞財經'],
    '4gtv-4gtv158' => ['寰宇財經台', '寰宇財經台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Global4.png','新聞財經'],
    'litv-ftv03' => ['VOA美國之音', 'VOA 美國之音', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/VOATV.png','新聞財經'],
    'litv-ftv07' => ['民視旅遊台', '民視旅遊', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV5.png','生活旅遊'],
    'litv-ftv09' => ['民視影劇台', '民視影劇', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV4.png','電影戲劇'],
    'litv-ftv10' => ['My Cinema Europe HD 我的歐洲電影', '我的歐洲電影', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/MyCinema.png','電影戲劇'],
    'litv-ftv13' => ['民視新聞台', '民視新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FTV1.png','新聞財經'],
    'litv-ftv15' => ['影迷數位紀實台', '影迷數位紀實台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/FANS2.png','紀實探索'],
    'litv-ftv16' => ['好消息', '好消息', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoodTV1.png','綜合其他'],
    'litv-ftv17' => ['好消息2台', '好消息2台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/GoodTV2.png','綜合其他'],
    'litv-xinchuang01' => ['龍華卡通台', '龍華卡通台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV9.png','兒童卡通'],
    'litv-xinchuang02' => ['龍華洋片台', '龍華洋片台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV2.png','電影戲劇'],
    'litv-xinchuang03' => ['龍華電影台', '龍華電影台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV1.png','電影戲劇'],
    'litv-xinchuang04' => ['博斯魅力台', '博斯魅力台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast6.png','體育競技'],
    'litv-xinchuang05' => ['博斯高球台', '博斯高球台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast3.png','體育競技'],
    'litv-xinchuang06' => ['博斯高球二台', '博斯高球二台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast4.png','體育競技'],
    'litv-xinchuang07' => ['博斯運動一台', '博斯運動一台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast1.png','體育競技'],
    'litv-xinchuang08' => ['博斯運動二台', '博斯運動二台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast2.png','體育競技'],
    'litv-xinchuang09' => ['博斯網球台', '博斯網球台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast5.png','體育競技'],
    'litv-xinchuang10' => ['博斯無限台', '博斯無限台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast7.png','體育競技'],
    'litv-xinchuang11' => ['龍華日韓台', '龍華日韓台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV5.png','電影戲劇'],
    'litv-xinchuang12' => ['龍華偶像台', '龍華偶像台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV6.png','電影戲劇'],
    'litv-xinchuang13' => ['博斯無限二台', '博斯無限二台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/sportcast8.png','體育競技'],
    'litv-longturn14' => ['寰宇新聞台', '寰宇新聞台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Global2.png','新聞財經'],
    'litv-xinchuang18' => ['龍華戲劇台', '龍華戲劇台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV4.png','電影戲劇'],
    'litv-xinchuang19' => ['SMART知識台', 'SMART知識台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/smarttv.png','生活旅遊'],
    'litv-xinchuang20' => ['ELTV英語學習台', 'ELTV英語學習台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/ELTA7.png','兒童卡通'],
    'litv-xinchuang21' => ['龍華經典台', '龍華經典台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/LTV7.png','電影戲劇'],
    'litv-xinchuang22' => ['台灣戲劇台', '台灣戲劇台', 'https://cdn.jsdelivr.net/gh/wanglindl/TVlogo@main/img/Taiwanxiju.png','電影戲劇'],
    'nnews-zh' => ['倪珍播新聞', '倪珍播新聞', 'https://p-cdnstatic.svc.litv.tv/pics/logo_litv_nnews_mobile.png','新聞財經'],
    'litv-fast1223' => ['Focus歡樂綜合台', 'Focus歡樂綜合台', 'https://p-cdnstatic.svc.litv.tv/pics/vod_channel/litv-fast1223_mobile.png','綜合其他'],
    'litv-fast1224' => ['Focus風采戲劇台', 'Focus風采戲劇台', 'https://p-cdnstatic.svc.litv.tv/pics/vod_channel/litv-fast1224_mobile.png','電影戲劇'],
    'litv-fast1225' => ['黃金八點檔', '黃金八點檔', 'https://p-cdnstatic.svc.litv.tv/pics/vod_channel/litv-fast1224_mobile.png','綜合其他']
];

$id = isset($_GET['id']) ? $_GET['id'] : null;

// 动态获取基础 URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_url = "$protocol://$host";
$current_script = basename($_SERVER['PHP_SELF']);

// 无参数时返回完整 M3U 频道列表
if (!$id) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "#EXTM3U\n";
    echo "#EXTM3U x-tvg-url=\"https://epg.iill.top/epg.xml\"\n";
    echo "\n";
    foreach ($channels as $key => $value) {
        $group = (isset($value[3]) && $value[3] !== '') ? $value[3] : 'LITV';
        echo '#EXTINF:-1 tvg-id="'.$value[0].'" tvg-name="'.$value[0].'" tvg-logo="'.$value[2].'" group-title="'.$group.'",'.$value[1]."\n";
        echo "$base_url/$current_script?id=" . urlencode($key) . "&token=" . urlencode($SECRET_TOKEN) . "\n";
    }
    exit;
}

// 检查频道 ID 是否有效
if (!isset($channels[$id])) {
    http_response_code(404);
    echo "Error: Channel not found.";
    exit;
}

/**
 * 发送请求获取串流网址
 */
function getLiTVStreamURL($assetId, $mediaType = 'channel', $puid = null) {
    global $API_URL, $COOKIES, $PUID;
    
    // 如果提供了新的 PUID，则使用
    if (!$puid) {
        $puid = $PUID;
    }
    
    // 准备请求资料
    $postData = [
        'AssetId' => $assetId,
        'MediaType' => $mediaType,
        'puid' => $puid
    ];
    
    // 将数组转换为 JSON 格式
    $payload = json_encode($postData);
    
    // 准备 cookies 字符串
    $cookieString = '';
    foreach ($COOKIES as $name => $value) {
        $cookieString .= "{$name}={$value}; ";
    }
    $cookieString = rtrim($cookieString, '; ');
    
    // 准备 headers
    $headers = [
        'Accept: application/json, text/plain, */*',
        'Accept-Encoding: gzip, deflate, br, zstd',
        'Accept-Language: zh-TW,zh-CN;q=0.9,zh;q=0.8,en-US;q=0.7,en;q=0.6',
        'Content-Type: application/json',
        'Origin: https://www.litv.tv',
        'Referer: https://www.litv.tv/channel/watch/litv-vchannel22',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36',
        'Priority: u=1, i'
    ];
    
    // 初始化 cURL
    $ch = curl_init();
    
    // 设置 cURL 选项
    $options = [
        CURLOPT_URL => $API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => array_merge($headers, ["Cookie: $cookieString"]),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING => '',
        CURLOPT_TIMEOUT => 30
    ];
    
    curl_setopt_array($ch, $options);
    
    // 执行请求
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL 错误: $error");
    }
    
    curl_close($ch);
    
    // 检查 HTTP 状态码
    if ($httpCode != 200) {
        throw new Exception("HTTP 错误: $httpCode");
    }
    
    // 解析 JSON 响应
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON 解析错误: " . json_last_error_msg());
    }
    
    // 检查是否成功取得 AssetURLs
    if (isset($data['result']['AssetURLs']) && !empty($data['result']['AssetURLs'])) {
        return $data['result']['AssetURLs'][0];
    } else {
        throw new Exception("无法取得 AssetURLs");
    }
}

// 使用 API 获取串流网址
try {
    // 获取串流网址
    $streamURL = getLiTVStreamURL($id, 'channel', $PUID);
    
    // 重定向到获取的串流网址
    header('Location: ' . $streamURL, true, 302);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
    exit;
}
?>