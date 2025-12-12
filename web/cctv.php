<?php
//本PHP首发于直播源论坛：https://bbs.livecodes.vip/，转载请注明出处，不要做那等偷摸小人！
date_default_timezone_set("Asia/Shanghai");

function java_string_hashcode(string $s): int {
    $h = 0;
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $h = (31 * $h + ord($s[$i]));
        $h &= 0xFFFFFFFF;
    }
    if ($h >= 0x80000000) {
        $h -= 0x100000000;
    }
    return $h;
}

function generate_random_imei(int $length = 15): string {
    $imei = '';
    for ($i = 0; $i < $length; $i++) {
        $imei .= random_int(0, 9);
    }
    return $imei;
}

function generate_utdid(): string {
    $timestamp = time();
    $timestamp_bytes = pack('N', $timestamp);

    $random_int = random_int(-2147483648, 2147483647);
    $random_bytes = pack('N', $random_int);

    $version_bytes = "\x03\x00";

    $imei = generate_random_imei();
    $imei_hash = java_string_hashcode($imei);
    $imei_hash_bytes = pack('N', $imei_hash);

    $data_to_sign = $timestamp_bytes . $random_bytes . $version_bytes . $imei_hash_bytes;

    // 原样作为 HMAC key 使用（不是十六进制）
    $hmac_key = "d6fc3a4a06adbde89223bvefedc24fecde188aaa9161";
    $hmac_sha1 = hash_hmac('sha1', $data_to_sign, $hmac_key, true);
    $hmac_base64 = base64_encode($hmac_sha1);

    $signature_hash = java_string_hashcode($hmac_base64);
    $signature_hash_bytes = pack('N', $signature_hash);

    $raw_utdid = $data_to_sign . $signature_hash_bytes;

    return base64_encode($raw_utdid);
}

function getCCTVNEWSPlayUrlPHP(string $articleId): string {
    $APP_KEY   = '20000008';
    $APP_VER   = '10.9.0';
    $FEATURES  = '27';
    $HMAC_KEY  = '3df8017cb9367f5997ab9e4b19c1e028';

    $UTDID     = generate_utdid();
    $TTID      = '702006@CCNews_Android_10.9.0';

    $t       = (string) time();
    $urlpath = 'articleId='.$articleId.'&channelId=1212&scene_type=6';
    $md5Content = md5($urlpath);

    $msg = $UTDID.'&&&'.$APP_KEY.'&'.$md5Content.'&'.$t
         .'&emas.feed.article.live.detail&1.0.0&&'.$TTID.'&&&'.$FEATURES;

    $sign = hash_hmac('sha256', $msg, $HMAC_KEY);

    $url = 'https://emas-api.cctvnews.cctv.com/gw/emas.feed.article.live.detail/1.0.0/?'.$urlpath;

    $ch = curl_init($url);
    $headers = [
        'appVersion: '.$APP_VER,
        'x-emas-gw-utdid: '.urlencode($UTDID),
        'utdid: '.$UTDID,
        'x-emas-gw-ttid: '.urlencode($TTID),
        'x-emas-gw-sign: '.$sign,
        'x-emas-gw-t: '.$t,
        'ua: Android/13 (HUAWEI cn.cntvnews;zh_CN) App/4.5.15 AliApp() HONOR-400/11358758 Channel/702006 language/zh-CN Device/CCNews CCNews/'.$APP_VER,
        'x-emas-gw-features: '.$FEATURES,
        'x-emas-gw-auth-ticket: 20000008',
        'x-emas-gw-appkey: '.$APP_KEY,
        'x-emas-gw-pv: 6.1',
        'timezone: 28800',
        'osVersion: 13',
        'User-Agent: MTOPSDK%2F3.0.6+%28Android%3B13%3BHUAWEI%3BHONOR-400%29',
        'Connection: keep-alive',
        'Host: emas-api.cctvnews.cctv.com',
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_ENCODING       => '', // 接受 gzip/deflate/br
        CURLOPT_TIMEOUT        => 15,
    ]);

    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || empty($body)) {
        return "请求失败, HTTP Code: $httpCode" . ($curlErr ? " ($curlErr)" : "");
    }

    $outer = json_decode($body, true);
    if (!isset($outer['response'])) {
        return '外层JSON解析失败';
    }

    $decoded = base64_decode($outer['response'], true);
    if ($decoded === false) {
        return 'Base64解码失败';
    }

    $inner = json_decode($decoded, true);
    if (!is_array($inner)) {
        return '内层JSON解析失败';
    }

    foreach (($inner['data']['live_room']['liveCameraList'] ?? []) as $cam) {
        foreach (($cam['pullUrlList'] ?? []) as $item) {
            if (($item['format'] ?? '') === 'HLS' && ($item['drm'] ?? 1) == 0) {
                foreach (($item['authResultUrl'] ?? []) as $au) {
                    if (!empty($au['authUrl'])) {
                        return $au['authUrl'];
                    }
                }
            }
        }
    }

    return '未找到有效的播放链接';
}

// ----------------- 频道映射 -----------------
$id = $_GET['id'] ?? 'cctv4k';
$map = [
    'cctv1'=>  '11200132825562653886',
    'cctv2'=>  '12030532124776958103',
    'cctv4'=>  '10620168294224708952',
    'cctv7'=>  '8516529981177953694',
    'cctv9'=>  '7252237247689203957',
    'cctv10'=> '14589146016461298119',
    'cctv12'=> '13180385922471124325',
    'cctv13'=> '16265686808730585228',
    'cctv17'=> '4496917190172866934',
    'cctv4k'=> '2127841942201075403',
];
$articleId = $map[$id] ?? $map['cctv4k'];

// ----------------- 简易文件缓存（10分钟） -----------------
$cacheDir  = __DIR__ . '/cctvnewsappcache';
$ttl       = 600; // 10分钟
if (!is_dir($cacheDir)) {
    // 递归创建目录
    @mkdir($cacheDir, 0775, true);
}
$safeId    = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $id);
$cacheFile = $cacheDir . '/' . $safeId . '.json';

// 命中且未过期 -> 直接跳转
if (is_file($cacheFile)) {
    $mtime = filemtime($cacheFile);
    if ($mtime !== false && (time() - $mtime) < $ttl) {
        $json = @file_get_contents($cacheFile);
        $arr  = $json ? json_decode($json, true) : null;
        if (is_array($arr) && !empty($arr['url']) && filter_var($arr['url'], FILTER_VALIDATE_URL)) {
            header('Cache-Control: no-store');
            header('Location: ' . $arr['url']);
            exit;
        }
    }
}

// 缓存未命中或已过期 -> 拉取新链接
$playUrl = getCCTVNEWSPlayUrlPHP($articleId);

// 成功拿到有效 URL 才写缓存
if ($playUrl && filter_var($playUrl, FILTER_VALIDATE_URL)) {
    $payload = [
        'id'       => $id,
        'url'      => $playUrl,
        'ts'       => time(),
        'articleId'=> $articleId,
    ];
    $tmpFile = $cacheFile . '.tmp';
    @file_put_contents($tmpFile, json_encode($payload, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), LOCK_EX);
    @rename($tmpFile, $cacheFile);

    header('Cache-Control: no-store');
    header('Location: ' . $playUrl);
    exit;
}

// 拿不到有效链接：返回错误信息（不写缓存）
http_response_code(502);
header('Content-Type: text/plain; charset=utf-8');
echo $playUrl ?: '获取播放链接失败';
