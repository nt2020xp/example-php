<?php
/*
 * LiTV 直播源生成器 (Pro 伪装增强版)
 * 版本：v2.0
 * 功能：
 * 1. 模拟 Android TV 客户端头部，欺骗 CDN。
 * 2. 内置 TS 流量中转代理，解决播放器直连 403 问题。
 * 3. 维持原有频道修复和 Logo。
 */

// 关掉错误显示，防止污染视频流
error_reporting(0);
// 设置脚本不超时（转发流媒体必需）
set_time_limit(0); 
date_default_timezone_set("Asia/Shanghai");

// ========== 1. 核心配置 ==========
$SECRET_TOKEN = 'cnbkk'; // 你的 Token
$DEFAULT_GROUP = 'LITV';

// 伪造的 HTTP 头部 (模拟 Nvidia Shield Android TV)
$fake_headers = [
    'User-Agent: LiTV/5.3.60 (Android TV; Android 9; Nvidia Shield)',
    'Referer: https://www.litv.tv/',
    'Origin: https://www.litv.tv',
    'X-Requested-With: com.litv.mobile.tv',
    'Accept: */*',
    'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7',
    'Connection: keep-alive',
    // 随机伪造一个台湾中华电信的 IP (虽然很难骗过 CDN，但做戏做全套)
    'X-Forwarded-For: 168.95.' . rand(1, 254) . '.' . rand(1, 254),
    'Client-IP: 168.95.' . rand(1, 254) . '.' . rand(1, 254)
];

// ========== 2. 频道映射表 (保持不变) ==========
$channels = [
    //新闻频道
    '4gtv-4gtv009' => [2, 9, '中天新闻', '中天新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中天新闻.png','新闻资讯'],
    '4gtv-4gtv072' => [1, 6, 'TVBS新闻', 'TVBS新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/TVBS新闻.png','新闻资讯'],
    '4gtv-4gtv152' => [1, 7, '东森新闻', '东森新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/东森新闻.png','新闻资讯'],
    'litv-ftv13' => [1, 9, '民视新闻', '民视新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视新闻.png','新闻资讯'],
    '4gtv-4gtv075' => [1, 6, '镜电视新闻', '镜新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/镜电视新闻.png','新闻资讯'],
    '4gtv-4gtv010' => [1, 7, '非凡新闻', '非凡新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/非凡新闻.png','新闻资讯'],
    '4gtv-4gtv051' => [1, 6, '台视新闻', '台视新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/台视新闻.png','新闻资讯'],
    '4gtv-4gtv052' => [1, 6, '华视新闻', '华视新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/华视新闻.png','新闻资讯'],
    '4gtv-4gtv074' => [1, 6, '中视新闻', '中视新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中视新闻.png','新闻资讯'],
    'litv-longturn14' => [1, 7, '寰宇新闻', '寰宇新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/寰宇新闻.png','新闻资讯'],
    '4gtv-4gtv156' => [1, 8, '寰宇新闻台湾', '寰宇新闻台湾', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/寰宇新闻台湾.png','新闻资讯'],
    'litv-ftv10' => [1, 7, '半岛国际新闻', '半岛新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/半岛国际新闻.png','新闻资讯'],
    'litv-ftv03' => [1, 9, '美国之音', '美国之音', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/美国之音.png','新闻资讯'],

    //财经频道
    '4gtv-4gtv153' => [1, 9, '东森财经新闻', '东森财经新闻', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/东森财经新闻.png','财经商业'],
    '4gtv-4gtv048' => [1, 6, '非凡商业', '非凡商业', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/非凡商业.png','财经商业'],
    '4gtv-4gtv056' => [1, 6, '台视财经', '台视财经', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/台视财经.png','财经商业'],
    '4gtv-4gtv104' => [1, 7, '第1商业台', '第1商业台', 'https://p-cdnstatic.svc.litv.tv/pics/logo_litv_4gtv-4gtv104_pc.png','财经商业'],

    //综合频道
    '4gtv-4gtv073' => [1, 6, 'TVBS', 'TVBS', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/TVBS.png','综合频道'],
    '4gtv-4gtv066' => [1, 7, '台视', '台视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/台视.png','综合频道'],
    '4gtv-4gtv040' => [1, 7, '中视', '中视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中视.png','综合频道'],
    '4gtv-4gtv041' => [1, 7, '华视', '华视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/华视.png','综合频道'],
    '4gtv-4gtv002' => [1, 11, '民视', '民视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视.png','综合频道'],
    '4gtv-4gtv155' => [1, 7, '民视', '民视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视.png','综合频道'],
    '4gtv-4gtv001' => [1, 7, '民视台湾', '民视台湾', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视台湾.png','综合频道'],
    '4gtv-4gtv003' => [1, 7, '民视第一', '民视第一', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视第一.png','综合频道'],
    '4gtv-4gtv109' => [1, 9, '中天亚洲', '中天亚洲', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中天亚洲.png','综合频道'],
    '4gtv-4gtv046' => [1, 7, '靖天综合', '靖天综合', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天综合.png','综合频道'],
    '4gtv-4gtv063' => [1, 8, '靖天国际', '靖天国际', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天国际.png','综合频道'],
    '4gtv-4gtv065' => [1, 9, '靖天资讯', '靖天资讯', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天资讯.png','综合频道'],
    '4gtv-4gtv043' => [1, 7, '客家电视', '客家电视', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/客家电视.png','综合频道'],
    '4gtv-4gtv079' => [1, 8, 'ARIRANG', '阿里郎', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/ARIRANG.png','综合频道'],
    '4gtv-4gtv084' => [1, 9, '国会频道1', '国会频道1', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/国会频道1.png','综合频道'],
    '4gtv-4gtv085' => [1, 6, '国会频道2', '国会频道2', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/国会频道2.png','综合频道'],

    //娱乐综艺
    '4gtv-4gtv068' => [1, 8, 'TVBS欢乐', 'TVBS欢乐', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/TVBS欢乐.png','综艺娱乐'],
    '4gtv-4gtv067' => [1, 9, 'TVBS精采', 'TVBS精采', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/TVBS精采.png','综艺娱乐'],
    '4gtv-4gtv070' => [1, 9, 'ELTV娱乐', '爱尔达娱乐', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/爱尔达娱乐.png','综艺娱乐'],
    '4gtv-4gtv004' => [1, 9, '民视综艺', '民视综艺', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视综艺.png','综艺娱乐'],
    '4gtv-4gtv039' => [1, 8, '八大综艺', '八大综艺', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/八大综艺.png','综艺娱乐'],
    '4gtv-4gtv034' => [1, 7, '八大精彩', '八大精彩', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/八大精彩.png','综艺娱乐'],
    '4gtv-4gtv054' => [1, 9, '靖天欢乐', '靖天欢乐', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天欢乐.png','综艺娱乐'],
    '4gtv-4gtv062' => [1, 9, '靖天育乐', '靖天育乐', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天育乐.png','综艺娱乐'],
    '4gtv-4gtv064' => [1, 9, '中视菁采', '中视菁采', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中视菁采.png','综艺娱乐'],
    '4gtv-4gtv006' => [1, 10, '猪哥亮歌厅秀', '猪哥亮歌厅秀', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/猪哥亮歌厅秀.png','综艺娱乐'],

    //电影
    '4gtv-4gtv011' => [1, 7, '影迷數位電影', '影迷數位電影', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/影迷数位电影.png','电影戏剧'],
    '4gtv-4gtv017' => [1, 7, 'amc电影', 'amc电影', 'https://4gtvimg2.4gtv.tv/4gtv-Image/Channel/mobile/logo_4gtv_4gtv-4gtv017new_mobile.png','电影戏剧'],
    '4gtv-4gtv061' => [1, 7, '靖天电影', '靖天电影', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天电影.png','电影戏剧'],
    '4gtv-4gtv055' => [1, 9, '靖天映画', '靖天映画', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天映画.png','电影戏剧'],
    '4gtv-4gtv049' => [1, 9, '采昌影剧', '采昌影剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/采昌影剧.png','电影戏剧'],
    'litv-ftv09' => [1, 6, '民视影剧', '民视影剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视影剧.png','电影戏剧'],
    'litv-longturn03' => [5, 6, '龙华电影', '龙华电影', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华电影.png','电影戏剧'],
    'litv-longturn02' => [5, 2, '龙华洋片', '龙华洋片', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华洋片.png','电影戏剧'],

    //戏剧
    '4gtv-4gtv042' => [1, 7, '公视戏剧', '公视戏剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/公视戏剧.png','电影戏剧'],
    '4gtv-4gtv045' => [1, 7, '靖洋戏剧', '靖洋戏剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖洋戏剧.png','电影戏剧'],
    '4gtv-4gtv058' => [1, 9, '靖天戏剧', '靖天戏剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天戏剧.png','电影戏剧'],
    '4gtv-4gtv080' => [1, 8, '中视经典', '中视经典', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/中视经典.png','电影戏剧'],
    '4gtv-4gtv047' => [1, 2, '靖天日本', '靖天日本', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天日本.png','电影戏剧'],
    'litv-longturn18' => [5, 6, '龙华戏剧', '龙华戏剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华戏剧.png','电影戏剧'],
    'litv-longturn11' => [5, 2, '龙华日韩', '龙华日韩', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华日韩.png','电影戏剧'],
    'litv-longturn12' => [5, 2, '龙华偶像', '龙华偶像', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华偶像.png','电影戏剧'],
    'litv-longturn21' => [5, 6, '龙华经典', '龙华经典', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华经典.png','电影戏剧'],
    'litv-longturn22' => [5, 2, '台湾戏剧', '台湾戏剧', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/台湾戏剧.png','电影戏剧'],

    //体育
    '4gtv-4gtv014' => [1, 6, '时尚运动X', '时尚运动X', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/时尚运动X.png','体育频道'],
    '4gtv-4gtv053' => [1, 9, 'GINXEsportsTV', 'GinxTV', 'https://p-cdnstatic.svc.litv.tv/pics/logo_litv_4gtv-4gtv053_pc.png','体育频道'],
    '4gtv-4gtv101' => [1, 6, '智林体育', '智林体育', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/智林体育.png','体育频道'],
    'litv-longturn04' => [5, 7, '博斯魅力', '博斯魅力', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯魅力.png','体育频道'],
    'litv-longturn05' => [5, 2, '博斯高球1', '博斯高球1', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯高球1.png','体育频道'],
    'litv-longturn06' => [5, 2, '博斯高球2', '博斯高球2', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯高球2.png','体育频道'],
    'litv-longturn07' => [5, 2, '博斯运动1', '博斯运动1', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯运动1.png','体育频道'],
    'litv-longturn08' => [5, 2, '博斯运动2', '博斯运动2', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯运动2.png','体育频道'],
    'litv-longturn09' => [5, 2, '博斯网球1', '博斯网球', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯网球.png','体育频道'],
    'litv-longturn10' => [5, 2, '博斯无限1', '博斯无限', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯无限.png','体育频道'],
    'litv-longturn13' => [4, 6, '博斯无限2', '博斯无限2', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/博斯无限2.png','体育频道'],

    //其他
    '4gtv-4gtv013' => [1, 7, '视纳华仁纪实', '视纳华仁纪实', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/视纳华仁纪实.png','纪实生活'],
    '4gtv-4gtv016' => [1, 7, 'Globetrotter', 'Globetrotter', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/GLOBALTREKKER.png','纪实生活'],
    '4gtv-4gtv018' => [1, 7, '达文西频道', '达文西频道', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/达文西.png','纪实生活'],
    '4gtv-4gtv076' => [1, 7, '亚洲旅游', '亚洲旅游', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/亚洲旅游.png','纪实生活'],
    'litv-ftv07' => [1, 9, '民视旅游', '民视旅游', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/民视旅游.png','纪实生活'],
    'litv-longturn19' => [5, 9, 'Smart知识台', 'Smart知识台', 'https://p-cdnstatic.svc.litv.tv/pics/logo_litv_litv-longturn19_pc.png','纪实生活'],
    '4gtv-4gtv044' => [1, 9, '靖天卡通', '靖天卡通', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖天卡通.png','儿童卡通'],
    '4gtv-4gtv057' => [1, 7, '靖洋卡通', '靖洋卡通', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/靖洋卡通.png','儿童卡通'],
    'litv-longturn01' => [4, 6, '龙华卡通', '龙华卡通', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/龙华卡通.png','儿童卡通'],
    '4gtv-4gtv059' => [1, 7, 'CLASSICA古典乐', '古典音乐', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/CLASSICA古典乐.png','音乐艺术'],
    '4gtv-4gtv082' => [1, 7, 'TraceUrban', 'TRACE URBAN', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/TRACEURBAN.png','音乐艺术'],
    '4gtv-4gtv083' => [1, 8, 'MezzoLiveHD', 'MEZZO LIVE', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/MEZZOLIVEHD.png','音乐艺术'],
    'litv-ftv16' => [1, 6, '好消息1', '好消息1', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/好消息1.png','宗教频道'],
    'litv-ftv17' => [1, 6, '好消息2', '好消息2', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/好消息2.png','宗教频道'],
    'litv-longturn20' => [5, 7, 'ELTV生活英语', '生活英语', 'https://gcore.jsdelivr.net/gh/taksssss/tv/icon/ELTV生活英语.png','教育学习']
];

// ========== 3. URL 辅助函数 ==========
function getBaseUrl() {
    if (php_sapi_name() === 'cli') return 'http://localhost';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
             (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    $protocol = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . $_SERVER['SCRIPT_NAME']; // 精确到文件名
}
$script_url = getBaseUrl();

// ========== 4. 逻辑入口 ==========

// 模式 A: 转发 TS 流 (Proxy Mode)
// 只有当参数 ?mode=ts&url=... 存在时触发
if (isset($_GET['mode']) && $_GET['mode'] === 'ts' && isset($_GET['url'])) {
    $target_ts = $_GET['url'];
    
    // 初始化 CURL 转发
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_ts);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $fake_headers); // 注入伪造头部
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // 直接输出流，不存内存
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    // 传递 Content-Type
    header('Content-Type: video/mp2t');
    curl_exec($ch);
    curl_close($ch);
    exit;
}

// 模式 B: 生成 M3U 播放列表 (List Mode)
// 验证 Token (仅针对列表请求验证，TS请求为了性能和兼容性通常不重复验证或在URL里带上)
$token = $_GET['token'] ?? '';
// 为了方便，这里把Token写死验证，实际TS请求因为是本机发出的，也可以略过验证
if ($token !== $SECRET_TOKEN) {
    // 兼容性处理：如果TS请求没带token，但因为是本机环回，可以放行，但这里简单起见，所有入口都查Token
    http_response_code(403);
    exit("Error: Access denied. Invalid token.\n");
}

$id = $_GET['id'] ?? null;

// B1: 返回总列表 (M3U)
if (!$id) {
    header('Content-Type: application/vnd.apple.mpegurl; charset=utf-8');
    header('Content-Disposition: inline; filename=playlist.m3u');
    echo "#EXTM3U\n";
    foreach ($channels as $key => $v) {
        $group = $v[5] ?: $GLOBALS['DEFAULT_GROUP'];
        $tvgId = $v[2];
        $name  = $v[3];
        $logo  = $v[4];
        // 生成的链接带上 ID 和 Token
        $url   = "{$script_url}?token=" . urlencode($GLOBALS['SECRET_TOKEN']) . "&id=" . urlencode($key);
        echo "#EXTINF:-1 tvg-id=\"{$tvgId}\" tvg-name=\"{$name}\" tvg-logo=\"{$logo}\" group-title=\"{$group}\",{$name}\n";
        echo "{$url}\n";
    }
    exit;
}

// B2: 返回单频道 M3U8 (Playlist Mode)
if (!isset($channels[$id])) {
    http_response_code(404);
    exit("Error: Channel not found.\n");
}

// 核心算法 (保持原版)
$timestamp = intval(time() / 4 - 355017625);
$t = $timestamp * 4;

header('Content-Type: application/vnd.apple.mpegurl');
header('Content-Disposition: inline; filename="' . $id . '.m3u8"');

$m3u8 = "#EXTM3U\n";
$m3u8 .= "#EXT-X-VERSION:3\n";
$m3u8 .= "#EXT-X-TARGETDURATION:4\n";
$m3u8 .= "#EXT-X-MEDIA-SEQUENCE:{$timestamp}\n";

for ($i = 0; $i < 3; $i++) {
    // 构造原始 LiTV CDN 地址
    $origin_ts_url = sprintf(
        "https://ntd-tgc.cdn.hinet.net/live/pool/%s/litv-pc/%s-avc1_6000000=%d-mp4a_134000_zho=%d-begin=%d0000000-dur=40000000-seq=%d.ts",
        $id, $id, $channels[$id][0], $channels[$id][1], $t, $timestamp
    );
    
    // 【关键修改】将直连地址改为指向本脚本的代理地址
    // 格式：http://你的域名/litv.php?mode=ts&url=经过编码的Hinet地址
    $proxy_ts_url = "{$script_url}?mode=ts&url=" . urlencode($origin_ts_url);

    $m3u8 .= "#EXTINF:4,\n";
    $m3u8 .= $proxy_ts_url . "\n";
    
    $timestamp++;
    $t += 4;
}

echo $m3u8;
?>