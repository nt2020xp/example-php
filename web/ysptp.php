<?php
// 開啟錯誤報告
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', 'cctv_proxy_error.log');

// TS 檔案代理轉發部分
if (isset($_GET['ts'])) {
    try {
        $ts_url = urldecode($_GET['ts']);
        $uid = $_GET['uid'] ?? '';
        
        if (empty($ts_url)) {
            throw new Exception('TS 檔案網址為空');
        }

        $headers = [
            "User-Agent: cctv_app_tv",
            "Referer: api.cctv.cn",
            "UID: " . $uid
        ];

        $ch = curl_init($ts_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $tsData = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('TS 檔案下載失敗: ' . curl_error($ch));
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode != 200) {
            throw new Exception('TS 檔案請求失敗，HTTP 狀態碼: ' . $httpCode);
        }
        
        header("Content-Type: video/MP2T");
        header("Cache-Control: max-age=3600");
        echo $tsData;
        exit;
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        die("TS 代理錯誤: " . $e->getMessage());
    }
}

// 應用程式常量定義
const APP_ID = '5f39826474a524f95d5f436eacfacfb67457c4a7';
const APP_VERSION = '1.3.4';
const UA = 'cctv_app_tv';
const REFERER = 'api.cctv.cn';
const PUB_KEY = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC/ZeLwTPPLSU7QGwv6tVgdawz9n7S2CxboIEVQlQ1USAHvBRlWBsU2l7+HuUVMJ5blqGc/5y3AoaUzPGoXPfIm0GnBdFL+iLeRDwOS1KgcQ0fIquvr/2Xzj3fVA1o4Y81wJK5BP8bDTBFYMVOlOoCc1ZzWwdZBYpb4FNxt//5dAwIDAQAB';

// API 網址常數
const URL_CLOUDWS_REGISTER = 'https://ytpcloudws.cctv.cn/cloudps/wssapi/device/v1/register';
const URL_GET_BASE = 'https://ytpaddr.cctv.cn/gsnw/live';
const URL_GET_APP_SECRET = 'https://ytpaddr.cctv.cn/gsnw/tpa/sk/obtain';
const URL_GET_STREAM = 'https://ytpvdn.cctv.cn/cctvmobileinf/rest/cctv/videoliveUrl/getstream';

// CCTV 頻道列表 (2024年最新)
$cctvList = [
    'cctv1'    => 'Live1717729995180256',
    'cctv2'    => 'Live1718261577870260',
    'cctv3'    => 'Live1718261955077261',
    'cctv4'    => 'Live1718276148119264',
    'cctv5'    => 'Live1719474204987287',
    'cctv5p'   => 'Live1719473996025286',
    'cctv7'    => 'Live1718276412224269',
    'cctv8'    => 'Live1718276458899270',
    'cctv9'    => 'Live1718276503187272',
    'cctv10'   => 'Live1718276550002273',
    'cctv11'   => 'Live1718276603690275',
    'cctv12'   => 'Live1718276623932276',
    'cctv13'   => 'Live1718276575708274',
    'cctv14'   => 'Live1718276498748271',
    'cctv15'   => 'Live1718276319614267',
    'cctv16'   => 'Live1718276256572265',
    'cctv17'   => 'Live1718276138318263',
    'cgtnen'   => 'Live1719392219423280',
    'cgtnfr'   => 'Live1719392670442283',
    'cgtnru'   => 'Live1719392779653284',
    'cgtnar'   => 'Live1719392885692285',
    'cgtnes'   => 'Live1719392560433282',
    'cgtndoc'  => 'Live1719392360336281',
    'cctv4k16' => 'Live1704966749996185',
    'cctv4k'   => 'Live1704872878572161',
    'cctv8k'   => 'Live1688400593818102',
];

/**
 * 生成隨機 Android ID
 */
function generateAndroidID() {
    return bin2hex(random_bytes(8));
}

/**
 * 使用公鑰加密數據
 */
function encryptByPublicKey($data, $pubKeyStr) {
    $pubKey = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" .
        chunk_split($pubKeyStr, 64, "\n") .
        "-----END PUBLIC KEY-----");
    if ($pubKey === false) {
        throw new Exception('公鑰格式錯誤: ' . openssl_error_string());
    }
    
    $encrypted = '';
    if (!openssl_public_encrypt($data, $encrypted, $pubKey)) {
        throw new Exception('加密失敗: ' . openssl_error_string());
    }
    
    return base64_encode($encrypted);
}

/**
 * 使用公鑰解密數據
 */
function decryptByPublicKey($encryptedStr, $pubKeyStr) {
    $pubKey = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" .
        chunk_split($pubKeyStr, 64) .
        "-----END PUBLIC KEY-----");
    if ($pubKey === false) {
        throw new Exception('公鑰格式錯誤: ' . openssl_error_string());
    }
    
    $decrypted = '';
    $encrypted = base64_decode($encryptedStr);
    if (!openssl_public_decrypt($encrypted, $decrypted, $pubKey)) {
        throw new Exception('解密失敗: ' . openssl_error_string());
    }
    
    return $decrypted;
}

/**
 * 發送 HTTP POST 請求
 */
function httpPost($url, $data, $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('HTTP 請求失敗: ' . curl_error($ch));
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 200) {
        throw new Exception('API 返回非 200 狀態碼: ' . $httpCode);
    }
    
    return $response;
}

/**
 * 獲取設備 GUID
 */
function getGUID($uid) {
    try {
        $encryptedUID = encryptByPublicKey($uid, PUB_KEY);
        $requestBody = json_encode([
            'device_name' => '央視頻電視投屏助手',
            'device_id'   => $encryptedUID,
        ]);
        
        $headers = [
            'Accept: application/json',
            'UID: ' . $uid,
            'Referer: ' . REFERER,
            'User-Agent: ' . UA,
            'Content-Type: application/json',
        ];
        
        $response = httpPost(URL_CLOUDWS_REGISTER, $requestBody, $headers);
        $result = json_decode($response, true);
        
        if (!isset($result['data']['guid'])) {
            throw new Exception('獲取 GUID 失敗: ' . $response);
        }
        
        return $result['data']['guid'];
    } catch (Exception $e) {
        throw new Exception('GUID 獲取流程錯誤: ' . $e->getMessage());
    }
}

/**
 * 獲取應用程式密鑰
 */
function getAppSecret($guid, $uid) {
    try {
        $encryptedGUID = encryptByPublicKey($guid, PUB_KEY);
        $requestBody = json_encode(['guid' => $encryptedGUID]);
        
        $headers = [
            'Accept: application/json',
            'UID: ' . $uid,
            'Referer: ' . REFERER,
            'User-Agent: ' . UA,
            'Content-Type: application/json',
        ];
        
        $response = httpPost(URL_GET_APP_SECRET, $requestBody, $headers);
        $result = json_decode($response, true);
        
        if (!isset($result['data']['appSecret'])) {
            throw new Exception('獲取 appSecret 失敗: ' . $response);
        }
        
        return decryptByPublicKey($result['data']['appSecret'], PUB_KEY);
    } catch (Exception $e) {
        throw new Exception('AppSecret 獲取流程錯誤: ' . $e->getMessage());
    }
}

/**
 * 獲取基礎 M3U 網址
 */
function getBaseM3uUrl($liveID, $uid) {
    try {
        $requestBody = json_encode([
            'rate'       => '',
            'systemType' => 'android',
            'model'      => '',
            'id'         => $liveID,
            'userId'     => '',
            'clientSign' => 'cctvVideo',
            'deviceId'   => [
                'serial'     => '',
                'imei'       => '',
                'android_id' => $uid,
            ],
        ]);
        
        $headers = [
            'Accept: application/json',
            'UID: ' . $uid,
            'Referer: ' . REFERER,
            'User-Agent: ' . UA,
            'Content-Type: application/json',
        ];
        
        $response = httpPost(URL_GET_BASE, $requestBody, $headers);
        $result = json_decode($response, true);
        
        if (!isset($result['data']['videoList'][0]['url'])) {
            throw new Exception('獲取基礎串流網址失敗: ' . $response);
        }
        
        return $result['data']['videoList'][0]['url'];
    } catch (Exception $e) {
        throw new Exception('基礎串流網址獲取流程錯誤: ' . $e->getMessage());
    }
}

/**
 * 獲取 M3U 播放列表網址
 */
function getM3uUrl($channelLiveID, $uid) {
    try {
        // 獲取設備 GUID
        $guid = getGUID($uid);
        
        // 獲取應用程式密鑰
        $appSecret = getAppSecret($guid, $uid);
        
        // 獲取基礎串流網址
        $baseUrl = getBaseM3uUrl($channelLiveID, $uid);
        
        // 生成隨機字串和簽名
        $appRandomStr = uniqid();
        $appSign = md5(APP_ID . $appSecret . $appRandomStr);

        // 構建請求數據
        $postData = [
            'appcommon' => json_encode([
                'adid' => $uid,
                'av' => APP_VERSION,
                'an' => '央視視頻電視投屏助手',
                'ap' => 'cctv_app_tv'
            ]),
            'url' => $baseUrl,
        ];
        
        // 設置請求標頭
        $headers = [
            'User-Agent: ' . UA,
            'Referer: ' . REFERER,
            'UID: ' . $uid,
            'APPID: ' . APP_ID,
            'APPSIGN: ' . $appSign,
            'APPRANDOMSTR: ' . $appRandomStr,
            'Content-Type: application/x-www-form-urlencoded',
        ];
        
        // 發送請求獲取串流網址
        $response = httpPost(URL_GET_STREAM, http_build_query($postData), $headers);
        $result = json_decode($response, true);
        
        if (!isset($result['url'])) {
            throw new Exception('獲取串流網址失敗: ' . $response);
        }
        
        $streamUrl = $result['url'];

        // 處理重定向直到獲取最終 m3u8 網址
        $ch = curl_init();
        $path = substr($streamUrl, 0, strrpos($streamUrl, '/') + 1);
        $maxRedirects = 5;
        $redirectCount = 0;
        
        while ($redirectCount < $maxRedirects) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $streamUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "User-Agent: " . UA,
                "Referer: " . REFERER,
                "UID: " . $uid,
            ]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            
            $data = curl_exec($ch);
            if (curl_errno($ch)) {
                throw new Exception('獲取 m3u8 內容失敗: ' . curl_error($ch));
            }
            
            if (preg_match('/(.*\.m3u8\?.*)/', $data, $matches)) {
                $m3u8_url = $matches[0];
                $streamUrl = $path . $m3u8_url;
                $redirectCount++;
            } else {
                break;
            }
        }
        
        if ($redirectCount >= $maxRedirects) {
            throw new Exception('達到最大重定向次數');
        }
        
        curl_close($ch);

        // 構建代理網址
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $proxyUrl = rtrim($protocol . "://" . $host . $scriptPath, '/') . '/' . basename($_SERVER['SCRIPT_NAME']);

        // 替換所有 .ts 網址為代理網址
        return preg_replace_callback('/([^\r\n]+\.ts(?:\?[^\r\n]*)?)/i', function($matches) use ($proxyUrl, $uid, $path) {
            $ts_full_url = $matches[1];
            if (stripos($ts_full_url, 'http') !== 0) {
                $ts_full_url = $path . $ts_full_url;
            }
            return $proxyUrl . "?ts=" . urlencode($ts_full_url) . "&uid=" . urlencode($uid);
        }, $data);
    } catch (Exception $e) {
        throw new Exception('M3U8 獲取流程錯誤: ' . $e->getMessage());
    }
}

// 主程序
try {
    ob_start();
    
    $uid = $_GET['uid'] ?? generateAndroidID();
    $id = $_GET['id'] ?? 'cctv4k';
    
    if (!isset($cctvList[$id])) {
        throw new Exception('無效的頻道 ID');
    }
    
    $m3u8Content = getM3uUrl($cctvList[$id], $uid);
    
    ob_end_clean();
    
    header("Content-Type: application/x-mpegURL");
    header("Content-Disposition: inline; filename=" . $id . ".m3u8");
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: 0");
    
    echo $m3u8Content;
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header("Content-Type: text/plain");
    die("錯誤: " . $e->getMessage());
}
