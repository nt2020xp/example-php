<?php
/**
 * 咪咕直播统一入口（支持多线路）
 * 用法：
 * - mgak.php?id=cctv1          播放CCTV1（自动选择线路）
 * - mgak.php?id=cctv1&line=2   指定线路2
 * - mgak.php?id=265183188      通过频道ID播放
 * - mgak.php?list              显示频道列表
 * - mgak.php?search=央视       搜索频道
 */

// 启用错误报告（生产环境请关闭）
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ==================== 频道映射表（支持多线路）====================
$CHANNEL_MAP = [
    // 央视频道
    'cctv1' => ['name' => 'CCTV1综合', 'ids' => [265183188, 265183189], 'no' => 1],
    'cctv1b' => ['name' => 'CCTV1综合(备)', 'ids' => [265183188, 265183669], 'no' => 1],
    'cctv2' => ['name' => 'CCTV2财经', 'ids' => [265667329, 265667330], 'no' => 2],
    'cctv3' => ['name' => 'CCTV3综艺', 'ids' => [265667206, 265667207], 'no' => 3],
    'cctv4' => ['name' => 'CCTV4中文国际', 'ids' => [265667639, 265667640], 'no' => 4],
    'cctv4o' => ['name' => 'CCTV4欧洲', 'ids' => [265667313, 265667314], 'no' => 4],
    'cctv4a' => ['name' => 'CCTV4美洲', 'ids' => [265667335, 265667336], 'no' => 4],
    'cctv5' => ['name' => 'CCTV5体育', 'ids' => [265667565, 265667566], 'no' => 5],
    'cctv5p' => ['name' => 'CCTV5+体育赛事', 'ids' => [265106763, 265125883], 'no' => 16],
    'cctv5p2' => ['name' => 'CCTV5+体育赛事2', 'ids' => [265106763, 265106764], 'no' => 16],
    'cctv6' => ['name' => 'CCTV6电影', 'ids' => [265667482, 265667483], 'no' => 6],
    'cctv7' => ['name' => 'CCTV7国防军事', 'ids' => [265667268, 265667269], 'no' => 7],
    'cctv8' => ['name' => 'CCTV8电视剧', 'ids' => [265667466, 265667467], 'no' => 8],
    'cctv9' => ['name' => 'CCTV9纪录', 'ids' => [265667202, 265667203], 'no' => 9],
    'cctv10' => ['name' => 'CCTV10科教', 'ids' => [265667631, 265667632], 'no' => 10],
    'cctv11' => ['name' => 'CCTV11戏曲', 'ids' => [265667429, 265667430], 'no' => 11],
    'cctv12' => ['name' => 'CCTV12社会与法', 'ids' => [265667607, 265667608], 'no' => 12],
    'cctv13' => ['name' => 'CCTV13新闻', 'ids' => [1139280199, 1139280200], 'no' => 13],
    'cctv14' => ['name' => 'CCTV14少儿', 'ids' => [265667325, 265667326], 'no' => 14],
    'cctv15' => ['name' => 'CCTV15音乐', 'ids' => [265667535, 265667536], 'no' => 15],
    'cctv17' => ['name' => 'CCTV17农业农村', 'ids' => [265667526, 265667527], 'no' => 17],
    'cctv9doc' => ['name' => 'CCTV9 Documentary', 'ids' => [265218920, 265218922], 'no' => null],
    'cgtna' => ['name' => 'CGTN阿拉伯语', 'ids' => [265219154, 265219155], 'no' => null],
    'cgtne' => ['name' => 'CGTN西班牙语', 'ids' => [265218872, 265218873], 'no' => null],
    'cctvf' => ['name' => 'CCTV法语', 'ids' => [265219025, 265219026], 'no' => null],
    'cctvr' => ['name' => 'CCTV俄语', 'ids' => [265218806, 265218807], 'no' => null],
    'lgs' => ['name' => 'CCTV老故事', 'ids' => [810326846, 810326847], 'no' => null],
    'fxzl' => ['name' => 'CCTV发现之旅', 'ids' => [810326624, 810326625], 'no' => null],
    'zxs' => ['name' => 'CCTV中学生', 'ids' => [810326679, 810326680], 'no' => null],
    
    // 卫视频道
    'dfws' => ['name' => '东方卫视', 'ids' => [1098710943, 1098710944], 'no' => 28],
    'jsws' => ['name' => '江苏卫视', 'ids' => [264104188, 264104189], 'no' => 32],
    'gdws' => ['name' => '广东卫视', 'ids' => [263541274, 275480030], 'no' => 31],
    'jxws' => ['name' => '江西卫视', 'ids' => [810783159, 810783160], 'no' => null],
    'hnws' => ['name' => '河南卫视', 'ids' => [1008007050, 1008007051], 'no' => null],
    'sxws' => ['name' => '陕西卫视', 'ids' => [816409120, 816409121], 'no' => null],
    'dwqws' => ['name' => '大湾区卫视', 'ids' => [265218882, 265218883], 'no' => null],
    'hubws' => ['name' => '湖北卫视', 'ids' => [1066830679, 1066830680], 'no' => null],
    'jlws' => ['name' => '吉林卫视', 'ids' => [1066865348, 1066865349], 'no' => null],
    'qhws' => ['name' => '青海卫视', 'ids' => [1066885177, 1066885178], 'no' => null],
    'dnws' => ['name' => '东南卫视', 'ids' => [810326620, 810454855], 'no' => null],
    'hinws' => ['name' => '海南卫视', 'ids' => [1066884988, 1066884989], 'no' => null],
    'hxws' => ['name' => '海峡卫视', 'ids' => [810326850, 810455033], 'no' => null],
    
    // 数字/特色频道
    'yxfy' => ['name' => '游戏风云', 'ids' => [265667664, 265667665], 'no' => null],
    'sszjd' => ['name' => '赛事最经典', 'ids' => [265218921, 265218922], 'no' => null],
    'ttmlh' => ['name' => '体坛名栏汇', 'ids' => [265218759, 265218760], 'no' => null],
    'shdy' => ['name' => '四海钓鱼', 'ids' => [265667494, 265667495], 'no' => null],
    'xpfy' => ['name' => '新片放映厅', 'ids' => [265218930, 265218931], 'no' => null],
    'zjsv' => ['name' => '追剧少女', 'ids' => [265218878, 265218879], 'no' => null],
    'chcjtyy' => ['name' => 'CHC家庭影院', 'ids' => [265667645, 265667646], 'no' => null],
    'chcdzdy' => ['name' => 'CHC动作电影', 'ids' => [265218967, 265218968], 'no' => null],
    'rbj' => ['name' => '热剧联播', 'ids' => [265218955, 265218956], 'no' => null],
    'gqdp' => ['name' => '高清大片', 'ids' => [265218862, 265218863], 'no' => null],
    'mgysdy' => ['name' => '咪咕云上电影院', 'ids' => [265219029, 265219030], 'no' => null],
    'jsjy' => ['name' => '江苏教育', 'ids' => [265219146, 265219147], 'no' => null],
    'sdjy' => ['name' => '山东教育', 'ids' => [265218942, 265218943], 'no' => null],
    
    // 熊猫频道
    'xmpt' => ['name' => '熊猫频道', 'ids' => [265667599, 265667600], 'no' => null],
    'xmpt1' => ['name' => '熊猫频道1', 'ids' => [265219065, 265219066], 'no' => null],
    'xmpt2' => ['name' => '熊猫频道2', 'ids' => [265218959, 265218960], 'no' => null],
    'xmpt3' => ['name' => '熊猫频道3', 'ids' => [265218910, 265218911], 'no' => null],
    'xmpt4' => ['name' => '熊猫频道4', 'ids' => [265218991, 265218992], 'no' => null],
    'xmpt6' => ['name' => '熊猫频道6', 'ids' => [265218934, 265218935], 'no' => null],
    'xmpt7' => ['name' => '熊猫频道7', 'ids' => [265219037, 265219038], 'no' => null],
    'xmpt8' => ['name' => '熊猫频道8', 'ids' => [265218971, 265218972], 'no' => null],
    'xmpt9' => ['name' => '熊猫频道9', 'ids' => [265218886, 265218887], 'no' => null],
    'xmpt10' => ['name' => '熊猫频道10', 'ids' => [265218794, 265218795], 'no' => null],
];

// ==================== 配置常量 ====================
const DEFAULT_UA = 'Dalvik/2.1.0 (Linux; U; Android 15; XIAOMI-15 Build/TP1A.220624.014)';
const DEFAULT_EDS_LOGIN_URL = 'http://aikanlive.miguvideo.com:8082/EDS/JSON/Login';
const DEFAULT_GETTIME_URL = 'http://aikanvod.miguvideo.com/video/p/getTime.jsp?vt=9';
const DEFAULT_BUSINESS_TYPE = 'BTV';
const LOGIN_STATE_CACHE_KEY = 'migu:login_state';
const LOGIN_STATE_TTL = 1200;
const PLAYURL_CACHE_PREFIX = 'migu:playurl:';
const PLAYURL_TTL = 600;

const AUTH_EXPIRED_CODES = [
    '-2', '125023001', '125023002', '125023003', '125023004',
    '125023005', '125023006', '125023007', '125023008', '125023009',
    '125023010', '125023011', '125023012',
];

// ==================== 辅助函数 ====================
function respond_json(array $payload, int $status = 200): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function respond_redirect(string $url, int $status = 302): void {
    $safeUrl = str_replace(["\r", "\n"], '', trim($url));
    if ($safeUrl === '') {
        respond_json(['ok' => false, 'error' => '无法跳转：playURL 为空'], 500);
    }
    if (PHP_SAPI === 'cli') {
        echo $safeUrl . PHP_EOL;
        exit;
    }
    if (!headers_sent()) {
        header('Location: ' . $safeUrl, true, $status);
    }
    exit;
}

function require_apcu(): void {
    if (!function_exists('apcu_fetch') || !filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN)) {
        respond_json(['ok' => false, 'error' => 'APCu 不可用：请确认已安装并启用 apcu 扩展'], 500);
    }
}

function normalize_cookie_pair(string $rawSetCookie): string {
    if ($rawSetCookie === '') return '';
    $parts = explode(';', $rawSetCookie, 2);
    return trim($parts[0]);
}

function pick_ret_code(array $data): string {
    foreach (['retCode', 'retcode', 'realStrRetCode'] as $k) {
        if (isset($data[$k]) && is_string($data[$k]) && $data[$k] !== '') {
            return $data[$k];
        }
    }
    if (isset($data['result']) && is_array($data['result'])) {
        foreach (['retCode', 'retcode'] as $k) {
            if (isset($data['result'][$k]) && is_string($data['result'][$k]) && $data['result'][$k] !== '') {
                return $data['result'][$k];
            }
        }
    }
    return '';
}

function pick_ret_msg(array $data): string {
    foreach (['retMsg', 'retmsg', 'message', 'msg'] as $k) {
        if (isset($data[$k]) && is_string($data[$k]) && $data[$k] !== '') {
            return $data[$k];
        }
    }
    if (isset($data['result']) && is_array($data['result'])) {
        foreach (['retMsg', 'retmsg', 'message', 'msg'] as $k) {
            if (isset($data['result'][$k]) && is_string($data['result'][$k]) && $data['result'][$k] !== '') {
                return $data['result'][$k];
            }
        }
    }
    return '';
}

function need_relogin(int $statusCode, string $retCode, string $retMsg): bool {
    if ($statusCode === 401 || $statusCode === 403) return true;
    if (in_array($retCode, AUTH_EXPIRED_CODES, true)) return true;
    $lower = strtolower($retMsg);
    foreach (['session', 'login', 'authenticate', 'expired', 'epgsession'] as $hint) {
        if (strpos($lower, $hint) !== false) return true;
    }
    return false;
}

function plus_one_numeric_string(string $n): string {
    if ($n === '' || !preg_match('/^\d+$/', $n)) {
        throw new RuntimeException('channelID 必须是纯数字字符串，当前值: ' . $n);
    }
    $chars = str_split($n);
    $carry = 1;
    for ($i = count($chars) - 1; $i >= 0; $i--) {
        $digit = ord($chars[$i]) - 48 + $carry;
        if ($digit >= 10) {
            $chars[$i] = '0';
            $carry = 1;
        } else {
            $chars[$i] = chr($digit + 48);
            $carry = 0;
            break;
        }
    }
    if ($carry === 1) array_unshift($chars, '1');
    return implode('', $chars);
}

function http_request(string $method, string $url, array $headers, ?string $body, int $timeout = 10): array {
    $ch = curl_init($url);
    if ($ch === false) return ['ok' => false, 'error' => 'curl_init 失败'];

    $headerLines = [];
    foreach ($headers as $name => $value) {
        $headerLines[] = $name . ': ' . $value;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headerLines,
    ]);

    if ($body !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => 'curl_exec 失败: ' . $err];
    }

    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $rawHeader = substr($raw, 0, $headerSize);
    $respBody = substr($raw, $headerSize);

    $headersOut = [];
    $setCookies = [];
    $lines = preg_split('/\r\n|\n|\r/', (string) $rawHeader);
    if ($lines !== false) {
        foreach ($lines as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = explode(':', $line, 2);
            $name = trim($k);
            $value = trim($v);
            if ($name === '') continue;
            $lowerName = strtolower($name);
            if (!isset($headersOut[$lowerName])) $headersOut[$lowerName] = $value;
            if ($lowerName === 'set-cookie') $setCookies[] = $value;
        }
    }

    return [
        'ok' => true,
        'status' => $status,
        'body' => $respBody,
        'headers' => $headersOut,
        'set_cookies' => $setCookies,
    ];
}

function post_json_follow_302_once(string $url, array $payload, array $headers, int $timeout = 10): array {
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) return ['ok' => false, 'error' => 'JSON 编码失败'];
    $resp = http_request('POST', $url, $headers, $body, $timeout);
    if (!$resp['ok']) return $resp;
    if ((int) $resp['status'] === 302 && isset($resp['headers']['location'])) {
        $redirected = (string) $resp['headers']['location'];
        $h2 = $headers;
        unset($h2['isEncrypt']);
        $resp = http_request('POST', $redirected, $h2, $body, $timeout);
    }
    return $resp;
}

function find_cookie_raw(array $setCookies, string $keyword): string {
    foreach ($setCookies as $line) {
        if (strpos($line, $keyword) !== false) return $line;
    }
    return '';
}

function build_cookie(array $state, bool $includeArrayid = true): string {
    $parts = ['JSESSIONID=' . (string) ($state['session_id'] ?? '')];
    if (!empty($state['set_cookie_raw'])) {
        $pair = normalize_cookie_pair((string) $state['set_cookie_raw']);
        if ($pair !== '') $parts[] = $pair;
    }
    if ($includeArrayid && !empty($state['arrayid_raw'])) {
        $pair = normalize_cookie_pair((string) $state['arrayid_raw']);
        if ($pair !== '') $parts[] = $pair;
    }
    return implode('; ', $parts);
}

function login_eds(array &$state, string $phone, int $timeout): void {
    $payload = [];
    if (preg_match('/^\d{11}$/', $phone)) $payload['UserID'] = $phone;
    $headers = [
        'User-Agent' => DEFAULT_UA,
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => '*/*',
        'Connection' => 'keep-alive',
        'isEncrypt' => '0',
    ];
    $resp = post_json_follow_302_once(DEFAULT_EDS_LOGIN_URL, $payload, $headers, $timeout);
    if (!$resp['ok']) throw new RuntimeException($resp['error']);
    if ((int) $resp['status'] < 200 || (int) $resp['status'] >= 300) {
        throw new RuntimeException('EDS 登录失败: HTTP ' . $resp['status']);
    }

    $data = json_decode((string) $resp['body'], true);
    if (!is_array($data)) throw new RuntimeException('EDS 返回非 JSON');

    $baseUrl = rtrim((string) ($data['epgurl'] ?? ''), '/');
    if ($baseUrl === '') throw new RuntimeException('EDS 未返回 epgurl');

    $state['base_url'] = $baseUrl;
    $state['login_url'] = rtrim((string) ($data['epghttpsurl'] ?? $baseUrl), '/');
    $state['set_cookie_raw'] = find_cookie_raw((array) ($resp['set_cookies'] ?? []), 'premsisdn');
}

function authenticate(array &$state, int $timeout): void {
    $baseUrl = (string) ($state['base_url'] ?? '');
    if ($baseUrl === '') throw new RuntimeException('base_url 为空，请先调用 login_eds');

    $payload = [
        'areaID' => '1',
        'locale' => '1',
        'loginType' => '3',
        'OSVersion' => '13',
        'physicalDeviceID' => '000000000000000',
        'templatelame' => 'default',
        'terminalType' => 'AndroidPhone',
        'terminalVendor' => 'XiaoMi',
        'timeZone' => '+0800',
        'userGroup' => '100',
        'softwareVersion' => '581$0$XM-15',
        'channelInfo' => '00990103',
    ];
    $headers = [
        'User-Agent' => DEFAULT_UA,
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => '*/*',
        'Connection' => 'keep-alive',
        'isEncrypt' => '0',
    ];

    $url = $baseUrl . '/EPG/VPE/PHONE/Authenticate';
    $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($body === false) throw new RuntimeException('Authenticate JSON 编码失败');
    $resp = http_request('POST', $url, $headers, $body, $timeout);
    if (!$resp['ok']) throw new RuntimeException($resp['error']);
    if ((int) $resp['status'] < 200 || (int) $resp['status'] >= 300) {
        throw new RuntimeException('Authenticate 失败: HTTP ' . $resp['status']);
    }

    $data = json_decode((string) $resp['body'], true);
    if (!is_array($data)) throw new RuntimeException('Authenticate 返回非 JSON');

    $retCode = pick_ret_code($data);
    if ($retCode !== '' && $retCode !== '0' && $retCode !== '000000000') {
        throw new RuntimeException('Authenticate retCode=' . $retCode);
    }

    $sessionId = '';
    if (isset($data['sessionID']) && is_string($data['sessionID'])) $sessionId = trim($data['sessionID']);
    elseif (isset($data['sessionid']) && is_string($data['sessionid'])) $sessionId = trim($data['sessionid']);
    elseif (isset($data['result']) && is_array($data['result'])) {
        if (isset($data['result']['sessionID']) && is_string($data['result']['sessionID'])) $sessionId = trim($data['result']['sessionID']);
        elseif (isset($data['result']['sessionid']) && is_string($data['result']['sessionid'])) $sessionId = trim($data['result']['sessionid']);
    }

    if ($sessionId === '') throw new RuntimeException('未拿到 sessionID/sessionid');
    $state['session_id'] = $sessionId;
}

function refresh_arrayid(array &$state, int $timeout): void {
    $headers = [
        'User-Agent' => DEFAULT_UA,
        'Accept' => '*/*',
        'Connection' => 'keep-alive',
        'EpgSession' => 'JSESSIONID=' . (string) ($state['session_id'] ?? ''),
        'Location' => (string) ($state['base_url'] ?? ''),
        'Cookie' => build_cookie($state, false),
    ];
    $resp = http_request('GET', DEFAULT_GETTIME_URL, $headers, null, $timeout);
    if (!$resp['ok']) throw new RuntimeException($resp['error']);
    $arr = find_cookie_raw((array) ($resp['set_cookies'] ?? []), 'arrayid');
    if ($arr !== '') $state['arrayid_raw'] = $arr;
}

function run_login_flow(string $phone, int $timeout): array {
    $state = ['base_url' => '', 'login_url' => '', 'session_id' => '', 'set_cookie_raw' => '', 'arrayid_raw' => ''];
    login_eds($state, $phone, $timeout);
    authenticate($state, $timeout);
    refresh_arrayid($state, $timeout);
    return $state;
}

function play_channel(array $state, string $businessType, string $channelID, string $mediaID, int $timeout): array {
    $baseUrl = (string) ($state['base_url'] ?? '');
    if ($baseUrl === '') throw new RuntimeException('base_url 为空');
    $parts = parse_url($baseUrl);
    if (!is_array($parts) || empty($parts['host'])) throw new RuntimeException('base_url 解析失败: ' . $baseUrl);
    $host = (string) $parts['host'];
    if (isset($parts['port'])) $host .= ':' . (string) $parts['port'];

    $bodyArr = ['IDType' => 0, 'businessType' => $businessType, 'channelID' => $channelID, 'mediaID' => $mediaID];
    $body = json_encode($bodyArr, JSON_UNESCAPED_UNICODE);
    if ($body === false) throw new RuntimeException('PlayChannel JSON 编码失败');

    $headers = [
        'User-Agent' => DEFAULT_UA,
        'isEncrypt' => '0',
        'EpgSession' => 'JSESSIONID=' . (string) ($state['session_id'] ?? ''),
        'Location' => $baseUrl,
        'Cookie' => build_cookie($state, true),
        'Content-Type' => 'application/json; charset=UTF-8',
        'Accept' => '*/*',
        'Host' => $host,
        'Connection' => 'keep-alive',
    ];
    $url = $baseUrl . '/VSP/V3/PlayChannel';
    $resp = http_request('POST', $url, $headers, $body, $timeout);
    if (!$resp['ok']) throw new RuntimeException($resp['error']);
    $data = json_decode((string) $resp['body'], true);
    if (!is_array($data)) $data = [];
    return ['status' => (int) $resp['status'], 'data' => $data, 'raw_body' => (string) $resp['body']];
}

function fetch_login_state(bool $force, string $phone, int $timeout, array &$debugLog): array {
    if (!$force) {
        $ok = false;
        $cached = apcu_fetch(LOGIN_STATE_CACHE_KEY, $ok);
        if ($ok && is_array($cached) && !empty($cached['base_url']) && !empty($cached['session_id'])) {
            $debugLog[] = '[cache] 命中登录态缓存';
            return $cached;
        }
    }
    $debugLog[] = '[login] 重新登录并刷新登录态缓存';
    $state = run_login_flow($phone, $timeout);
    apcu_store(LOGIN_STATE_CACHE_KEY, $state, LOGIN_STATE_TTL);
    return $state;
}

// ==================== 获取直播流（支持多线路自动切换）====================
function get_play_url_with_fallback($channelIds, $businessType, $phone, $force, $timeout, &$debugLog) {
    $lastError = null;
    
    foreach ($channelIds as $index => $channelId) {
        $lineNum = $index + 1;
        $debugLog[] = "[line{$lineNum}] 尝试线路，频道ID: {$channelId}";
        
        try {
            $playCacheKey = PLAYURL_CACHE_PREFIX . $channelId;
            $ok = false;
            $cachedPlayURL = apcu_fetch($playCacheKey, $ok);
            
            if ($ok && is_string($cachedPlayURL) && $cachedPlayURL !== '') {
                $debugLog[] = "[line{$lineNum}] 命中缓存";
                return ['playURL' => $cachedPlayURL, 'channelId' => $channelId, 'line' => $lineNum, 'source' => 'cache'];
            }
            
            $state = fetch_login_state($force, $phone, $timeout, $debugLog);
            $mid = plus_one_numeric_string((string)$channelId);
            $playResp = play_channel($state, $businessType, (string)$channelId, $mid, $timeout);
            $retCode = pick_ret_code((array) $playResp['data']);
            $retMsg = pick_ret_msg((array) $playResp['data']);
            
            if (need_relogin((int) $playResp['status'], $retCode, $retMsg)) {
                $debugLog[] = "[line{$lineNum}] 会话失效，重新登录";
                $state = run_login_flow($phone, $timeout);
                apcu_store(LOGIN_STATE_CACHE_KEY, $state, LOGIN_STATE_TTL);
                $playResp = play_channel($state, $businessType, (string)$channelId, $mid, $timeout);
            }
            
            $data = (array) $playResp['data'];
            $playURL = isset($data['playURL']) && is_string($data['playURL']) ? trim($data['playURL']) : '';
            
            if ($playURL !== '') {
                apcu_store($playCacheKey, $playURL, PLAYURL_TTL);
                $debugLog[] = "[line{$lineNum}] 成功获取播放地址";
                return ['playURL' => $playURL, 'channelId' => $channelId, 'line' => $lineNum, 'source' => 'api'];
            } else {
                $debugLog[] = "[line{$lineNum}] 返回空地址，retCode: {$retCode}, retMsg: {$retMsg}";
                $lastError = "线路{$lineNum}返回空地址: {$retMsg}";
            }
        } catch (Throwable $e) {
            $debugLog[] = "[line{$lineNum}] 异常: " . $e->getMessage();
            $lastError = "线路{$lineNum}异常: " . $e->getMessage();
        }
    }
    
    throw new RuntimeException("所有线路均失败，最后错误: {$lastError}");
}

// ==================== 频道列表HTML ====================
function render_channel_list_html($channels) {
    $categories = [
        'cctv' => ['name' => '📺 央视频道', 'keys' => ['cctv1','cctv2','cctv3','cctv4','cctv5','cctv5p','cctv6','cctv7','cctv8','cctv9','cctv10','cctv11','cctv12','cctv13','cctv14','cctv15','cctv17','cgtna','cgtne','cctvf','cctvr']],
        'weishi' => ['name' => '⭐ 卫视频道', 'keys' => ['dfws','jsws','gdws','jxws','hnws','sxws','dwqws','hubws','jlws','qhws','dnws','hinws','hxws']],
        'digital' => ['name' => '🎬 数字频道', 'keys' => ['yxfy','sszjd','ttmlh','shdy','xpfy','zjsv','chcjtyy','chcdzdy','rbj','gqdp','mgysdy']],
        'panda' => ['name' => '🐼 熊猫频道', 'keys' => ['xmpt','xmpt1','xmpt2','xmpt3','xmpt4','xmpt6','xmpt7','xmpt8','xmpt9','xmpt10']],
    ];
    
    $html = '<div class="categories">';
    foreach ($categories as $cat) {
        $html .= '<div class="category"><h3>' . $cat['name'] . '</h3><div class="channel-grid">';
        foreach ($cat['keys'] as $key) {
            if (isset($channels[$key])) {
                $ch = $channels[$key];
                $channelNo = $ch['no'] ? "[{$ch['no']}]" : "";
                $html .= sprintf(
                    '<a href="?id=%s" class="channel-card" data-key="%s">
                        <div class="channel-no">%s</div>
                        <div class="channel-name">%s</div>
                        <div class="channel-lines">%d线路</div>
                    </a>',
                    $key, $key, $channelNo, htmlspecialchars($ch['name']), count($ch['ids'])
                );
            }
        }
        $html .= '</div></div>';
    }
    $html .= '</div>';
    return $html;
}

// ==================== 主函数 ====================
function main(): void {
    global $CHANNEL_MAP;
    
    $action = $_GET['action'] ?? '';
    $id = $_GET['id'] ?? '';
    $line = isset($_GET['line']) ? (int)$_GET['line'] : 0;
    $jsonMode = isset($_GET['json']) && $_GET['json'] == '1';
    $debug = isset($_GET['debug']) && $_GET['debug'] == '1';
    
    // 显示频道列表
    if (isset($_GET['list']) || $action === 'list') {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>咪咕直播 - 频道列表（多线路）</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; padding: 20px; }
                .container { max-width: 1400px; margin: 0 auto; }
                .header { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); border-radius: 20px; padding: 20px; margin-bottom: 20px; text-align: center; }
                h1 { color: #fff; margin-bottom: 10px; }
                .subtitle { color: #aaa; }
                .search-box { width: 100%; max-width: 400px; margin: 20px auto 0; padding: 12px 20px; font-size: 16px; border: none; border-radius: 50px; background: rgba(255,255,255,0.2); color: white; outline: none; }
                .search-box::placeholder { color: rgba(255,255,255,0.6); }
                .categories { display: flex; flex-direction: column; gap: 20px; }
                .category { background: rgba(255,255,255,0.05); border-radius: 20px; padding: 20px; backdrop-filter: blur(5px); }
                .category h3 { color: #ff6b6b; margin-bottom: 15px; font-size: 20px; }
                .channel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; }
                .channel-card { display: block; background: rgba(255,255,255,0.1); border-radius: 12px; padding: 12px; text-align: center; text-decoration: none; transition: all 0.3s; position: relative; }
                .channel-card:hover { background: #ff6b6b; transform: translateY(-3px); }
                .channel-no { color: #ff6b6b; font-size: 11px; margin-bottom: 5px; }
                .channel-name { color: white; font-size: 13px; font-weight: 500; }
                .channel-lines { color: rgba(255,255,255,0.5); font-size: 10px; margin-top: 5px; }
                .channel-card:hover .channel-no { color: white; }
                .player-container { position: fixed; bottom: 0; left: 0; right: 0; background: #000; z-index: 1000; transform: translateY(100%); transition: transform 0.3s; }
                .player-container.active { transform: translateY(0); }
                .player-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background: rgba(0,0,0,0.9); color: white; }
                .line-info { font-size: 12px; color: #ff6b6b; }
                .close-player { background: #dc3545; border: none; color: white; padding: 5px 15px; border-radius: 5px; cursor: pointer; }
                .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; }
                #videoPlayer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
                @media (max-width: 768px) { .channel-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); } }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📺 咪咕直播（多线路自动切换）</h1>
                    <p class="subtitle">点击频道开始观看 | 支持多线路自动故障转移</p>
                    <input type="text" class="search-box" id="searchInput" placeholder="搜索频道...">
                </div>
                <div id="channelList">' . render_channel_list_html($CHANNEL_MAP) . '</div>
            </div>
            <div class="player-container" id="playerContainer">
                <div class="player-header">
                    <span id="currentChannel">正在播放...</span>
                    <span id="lineInfo" class="line-info"></span>
                    <button class="close-player" onclick="closePlayer()">关闭</button>
                </div>
                <div class="video-wrapper">
                    <video id="videoPlayer" controls autoplay></video>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
            <script>
                function searchChannel() { const keyword = document.getElementById("searchInput").value.toLowerCase(); document.querySelectorAll(".channel-card").forEach(card => { card.style.display = card.textContent.toLowerCase().includes(keyword) ? "block" : "none"; }); }
                document.getElementById("searchInput").addEventListener("input", searchChannel);
                async function playChannel(channelKey) {
                    const container = document.getElementById("playerContainer");
                    const video = document.getElementById("videoPlayer");
                    const currentSpan = document.getElementById("currentChannel");
                    const lineInfo = document.getElementById("lineInfo");
                    container.classList.add("active");
                    currentSpan.textContent = "加载中...";
                    lineInfo.textContent = "";
                    const response = await fetch(`?id=${channelKey}&json=1`);
                    const data = await response.json();
                    if (data.ok && data.playURL) {
                        currentSpan.textContent = data.channelName || channelKey;
                        lineInfo.textContent = `线路${data.line} (${data.source})`;
                        if (Hls.isSupported()) { const hls = new Hls(); hls.loadSource(data.playURL); hls.attachMedia(video); }
                        else if (video.canPlayType("application/vnd.apple.mpegurl")) video.src = data.playURL;
                        else alert("您的浏览器不支持HLS播放");
                    } else alert("获取播放地址失败: " + (data.error || "未知错误"));
                }
                function closePlayer() { const container = document.getElementById("playerContainer"); const video = document.getElementById("videoPlayer"); container.classList.remove("active"); video.pause(); video.src = ""; }
                document.querySelectorAll(".channel-card").forEach(card => { card.addEventListener("click", function(e) { e.preventDefault(); playChannel(this.dataset.key); }); });
            </script>
        </body>
        </html>';
        exit;
    }
    
    // 搜索频道
    if ($action === 'search' && isset($_GET['q'])) {
        $keyword = strtolower(trim($_GET['q']));
        $results = [];
        foreach ($CHANNEL_MAP as $key => $ch) {
            if (strpos(strtolower($ch['name']), $keyword) !== false || strpos($key, $keyword) !== false) {
                $results[] = ['key' => $key, 'ids' => $ch['ids'], 'name' => $ch['name'], 'no' => $ch['no']];
            }
        }
        respond_json(['ok' => true, 'results' => $results]);
    }
    
    // 获取频道信息
    if ($id === '') {
        // 默认播放CCTV1
        $id = 'cctv1';
    }
    
    if (!isset($CHANNEL_MAP[$id])) {
        // 尝试作为数字ID查找
        $found = null;
        foreach ($CHANNEL_MAP as $key => $ch) {
            if (in_array($id, $ch['ids'])) {
                $found = $key;
                break;
            }
        }
        if ($found) {
            $id = $found;
        } else {
            respond_json(['ok' => false, 'error' => '未知频道: ' . $id], 400);
        }
    }
    
    $channel = $CHANNEL_MAP[$id];
    $channelName = $channel['name'];
    $channelIds = $channel['ids'];
    
    // 指定线路
    if ($line > 0 && $line <= count($channelIds)) {
        $channelIds = [$channelIds[$line - 1]];
    }
    
    // 获取直播流
    require_apcu();
    
    $businessType = DEFAULT_BUSINESS_TYPE;
    $force = isset($_GET['force']) && $_GET['force'] == '1';
    $phone = $_GET['phone'] ?? '';
    $timeout = 10;
    $debugLog = [];
    
    try {
        $result = get_play_url_with_fallback($channelIds, $businessType, $phone, $force, $timeout, $debugLog);
        
        if (!$jsonMode) {
            respond_redirect($result['playURL'], 302);
        }
        
        respond_json([
            'ok' => true,
            'source' => $result['source'],
            'channelId' => $result['channelId'],
            'channelName' => $channelName,
            'line' => $result['line'],
            'totalLines' => count($channel['ids']),
            'playURL' => $result['playURL'],
            'debug' => $debug ? $debugLog : null
        ], 200);
        
    } catch (Throwable $e) {
        respond_json([
            'ok' => false,
            'error' => $e->getMessage(),
            'channelName' => $channelName,
            'totalLines' => count($channel['ids']),
            'debug' => $debug ? $debugLog : null
        ], 500);
    }
}

main();