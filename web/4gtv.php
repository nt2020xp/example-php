<?php
/**
 * 4GTV API 解析器 (PHP 完整轉換版)
 */

// --- 參數設定區 ---
$jz_mode = 1; // 1: 獲取播放地址 | 3: 獲取頻道列表
$jz_path = "path/to/js"; 

// 測試 URL (請確保格式包含 channel/xxxx 及 ch=數字)
$current_url = "https://4gtv.tv"; 
// -----------------

// 執行並輸出結果
$result = main($jz_mode, $current_url, $jz_path);
header('Content-Type: application/json; charset=utf-8');
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
 * 請求真實播放地址 (Mode 1)
 */
function GetPlayUrl($url) {
    // 1. 強力解析 URL 中的 ch 參數
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';

    // 2. 強力解析 URL 中的 Asset ID (例如 4gtv-4gtv001)
    $assetId = '';
    if (preg_match('/channel\/([^\/\?]+)/', $url, $matches)) {
        $assetId = $matches[1];
    }

    // 3. 驗證解析結果
    if (empty($ch) || empty($assetId)) {
        return [
            "error" => "URL 解析失敗",
            "msg" => "請確保 URL 包含 '/channel/xxx' 以及 '?ch=yyy'",
            "debug" => ["parsed_ch" => $ch, "parsed_assetId" => $assetId, "input_url" => $url]
        ];
    }

    // 4. 生成 4gtv_auth 與動態金鑰
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
        "clsAPP_IDENTITY_VALIDATE_ARUS" => [
            "fsVALUE" => "",
            "fsENC_KEY" => $encKey
        ],
        "fsDEVICE_TYPE" => "mobile"
    ];

    // 5. 發送 API 請求
    $ch_curl = curl_init("https://4gtv.tv");
    curl_setopt($ch_curl, CURLOPT_POST, 1);
    curl_setopt($ch_curl, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch_curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_curl, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch_curl);
    $httpCode = curl_getinfo($ch_curl, CURLINFO_HTTP_CODE);
    curl_close($ch_curl);

    $json = json_decode($resp, true);
    $data = $json['Data'] ?? null;

    if (!$data || !isset($data['flstURLs']) || empty($data['flstURLs'])) {
        return [
            "status" => "error",
            "message" => "無法取得直播源！",
            "api_msg" => $json['Message'] ?? "HTTP_$httpCode",
            "debug" => ["assetId" => $assetId, "ch" => $ch]
        ];
    }

    $urls = $data['flstURLs'];
    $index = rand(0, count($urls) - 1);
    return ["url" => $urls[$index]];
}

/**
 * 4gtv_auth 核心加解密邏輯
 */
function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encDataB64 = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKeyB64 = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIVB64 = "eGV/TEdmfF1eSEFnYFR7Xw==";

    // Base64 Decode & XOR
    $data = Base64toXOR($encDataB64, $xorKey);
    $key = Base64toXOR($encKeyB64, $xorKey);
    $iv = Base64toXOR($encIVB64, $xorKey);

    // AES-256-CBC Decrypt
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    // SHA512 Hash
    $today = date("Ymd");
    $hash = hash('sha512', $today . $clean);
    return base64_encode(hex2bin($hash));
}

/**
 * 頻道列表 (Mode 3)
 */
function GetChannelList($path) {
    $headers = ["User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"];
    $groups = [];

    // 獲取飛速看
    $resp1 = http_get("https://4gtv.tv", $headers);
    $data1 = json_decode($resp1, true)['Data'] ?? [];
    $fastChannels = [];
    foreach ($data1 as $item) {
        $fastChannels[] = formatChannel($item, $path);
    }
    $groups[] = ["name" => "飛速看", "channels" => $fastChannels];

    // 獲取一般分類
    $resp2 = http_get("https://4gtv.tv", $headers);
    $data2 = json_decode($resp2, true)['Data'] ?? [];
    
    $types = [];
    foreach ($data2 as $item) {
        $t = GetTypeName($item['fsTYPE_NAME']);
        if (!isset($types[$t])) $types[$t] = [];
        $types[$t][] = formatChannel($item, $path);
    }

    foreach ($types as $name => $chans) {
        $groups[] = ["name" => $name, "channels" => $chans];
    }

    return ["groups" => $groups];
}

/**
 * 輔助工具函數
 */
function formatChannel($item, $path) {
    return [
        "name" => $item['fsNAME'],
        "logo" => $item['fsLOGO_MOBILE'],
        "tvg" => $item['fsNAME'],
        "seasons" => [[
            "episodes" => [[
                "links" => [[
                    "url" => "https://4gtv.tv" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID'],
                    "js" => $path
                ]]
            ]]
        ]]
    ];
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = '';
    $kLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $kLen];
    }
    return $out;
}

function BuildencKey($len) {
    $min = pow(10, $len - 1);
    $max = pow(10, $len) - 1;
    return mt_rand($min, $max);
}

function GetTypeName($str) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    return implode('', array_slice($matches[0], 0, 2));
}

function http_get($url, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
?>
