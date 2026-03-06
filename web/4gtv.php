<?php
/**
 * 4GTV 自動解析播放器 (PHP 完整版)
 */

// --- 參數設定區 ---
$jz_mode = 1; // 1: 獲取播放地址 | 3: 獲取頻道列表
$jz_path = "path/to/js"; 

// 如果只給網域，程式會自動抓取清單中的第一個頻道來播放
$current_url = "https://4gtv.tv"; 
// -----------------

header('Content-Type: application/json; charset=utf-8');

// 自動化邏輯：如果 mode=1 但網址不完整，先去抓清單
if ($jz_mode == 1 && (strpos($current_url, 'ch=') === false)) {
    $list = GetChannelList($jz_path);
    if (!empty($list['groups'][0]['channels'][0]['seasons'][0]['episodes'][0]['links'][0]['url'])) {
        $current_url = $list['groups'][0]['channels'][0]['seasons'][0]['episodes'][0]['links'][0]['url'];
    } else {
        echo json_encode(["error" => "自動抓取清單失敗，請檢查網路連接"]);
        exit;
    }
}

// 執行主程序
$result = main($jz_mode, $current_url, $jz_path);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

/**
 * 主入口
 */
function main($mode, $url, $path) {
    if ($mode == 3) {
        return GetChannelList($path);
    } else if ($mode == 1) {
        return GetPlayUrl($url);
    }
    return ["error" => "未知的 mode"];
}

/**
 * 請求播放地址 (Mode 1)
 */
function GetPlayUrl($url) {
    // 1. 解析 ch 參數
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';

    // 2. 解析 Asset ID
    $assetId = '';
    if (preg_match('/channel\/([^\/\?]+)/', $url, $matches)) {
        $assetId = $matches[1];
    }

    if (empty($ch) || empty($assetId)) {
        return ["error" => "URL 解析失敗", "url" => $url];
    }

    // 3. 生成 Auth 與 Key
    $auth = Get4gtvauth();
    $encKey = BuildencKey(4) . "B" . BuildencKey(3) . "-" . BuildencKey(2) . "FA-45E8-8FA8-5C" . BuildencKey(6) . "A" . BuildencKey(3);

    $headers = [
        "Content-Type: application/json",
        "fsenc_key: $encKey",
        "fsdevice: iOS",
        "4gtv_auth: $auth",
        "User-Agent: okhttp/3.12.11",
        "fsversion: 3.1.0"
    ];

    $postData = [
        "fsASSET_ID" => $assetId,
        "fnCHANNEL_ID" => (int)$ch,
        "clsAPP_IDENTITY_VALIDATE_ARUS" => ["fsVALUE" => "", "fsENC_KEY" => $encKey],
        "fsDEVICE_TYPE" => "mobile"
    ];

    $resp = http_post("https://4gtv.tv", $headers, json_encode($postData));
    $json = json_decode($resp, true);
    $data = $json['Data'] ?? null;

    if (!$data || empty($data['flstURLs'])) {
        return ["error" => "無法取得直播源", "api_msg" => $json['Message'] ?? "API Error"];
    }

    return ["url" => $data['flstURLs'][rand(0, count($data['flstURLs']) - 1)]];
}

/**
 * 4gtv_auth 生成邏輯
 */
function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encDataB64 = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKeyB64 = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIVB64 = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = Base64toXOR($encDataB64, $xorKey);
    $key = Base64toXOR($encKeyB64, $xorKey);
    $iv = Base64toXOR($encIVB64, $xorKey);

    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    $hash = hash('sha512', date("Ymd") . $clean);
    return base64_encode(hex2bin($hash));
}

/**
 * 獲取頻道列表 (Mode 3)
 */
function GetChannelList($path) {
    $headers = ["User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15)"];
    $groups = [];

    // 抓取飛速看
    $resp = http_get("https://4gtv.tv", $headers);
    $data = json_decode($resp, true)['Data'] ?? [];
    $chans = [];
    foreach ($data as $item) { $chans[] = formatCh($item, $path); }
    $groups[] = ["name" => "飛速看", "channels" => $chans];

    return ["groups" => $groups];
}

/**
 * 輔助函數
 */
function formatCh($item, $path) {
    return [
        "name" => $item['fsNAME'],
        "seasons" => [["episodes" => [["links" => [["url" => "https://4gtv.tv".$item['fs4GTV_ID']."?set=1&ch=".$item['fnID']]]]]]]]
    ];
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = '';
    for ($i = 0; $i < strlen($raw); $i++) { $out .= $raw[$i] ^ $key[$i % strlen($key)]; }
    return $out;
}

function BuildencKey($len) { return mt_rand(pow(10, $len - 1), pow(10, $len) - 1); }

function http_get($url, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $r = curl_exec($ch); curl_close($ch); return $r;
}

function http_post($url, $headers, $body) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $r = curl_exec($ch); curl_close($ch); return $r;
}
?>
