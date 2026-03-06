<?php

/**
 * 4GTV API 解析器 (PHP 轉換版)
 */

// 模擬 ctx 與 jz 的環境變數
$jz_mode = 3; // 3 為獲取列表, 1 為獲取播放位址
$jz_path = "path/to/js"; 
$current_url = "https://4gtv.tv"; // 測試用

// 執行主程序
$result = main($jz_mode, $current_url, $jz_path);
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

function main($mode, $url, $path) {
    if ($mode == 3) {
        return GetChannelList($path);
    } else if ($mode == 1) {
        return GetPlayUrl($url);
    }
}

/* =========================
 * 頻道分組
 * ========================= */
function GetChannelList($path) {
    $headers = [
        "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"
    ];

    $groups = [];

    // 1. 獲取飛速看頻道
    $api1 = "https://api2.4gtv.tv/Channel/GetChannelFastTV";
    $resp1 = http_get($api1, $headers);
    $data1 = json_decode($resp1, true)['Data'] ?? [];

    $fast_channels = [];
    foreach ($data1 as $item) {
        $fast_channels[] = build_channel_item($item, $path);
    }
    $groups[] = ["name" => "飛速看", "channels" => $fast_channels];

    // 2. 獲取所有頻道並按類型分類
    $api2 = "https://api2.4gtv.tv/Channel/GetChannelBySetId/1/pc/L";
    $resp2 = http_get($api2, $headers);
    $data2 = json_decode($resp2, true)['Data'] ?? [];

    $typeList = [];
    foreach ($data2 as $item) {
        $typeName = GetTypeName($item['fsTYPE_NAME']);
        if (!in_array($typeName, $typeList)) {
            $typeList[] = $typeName;
        }
    }

    foreach ($typeList as $type) {
        $channels = [];
        foreach ($data2 as $item) {
            if ($type === GetTypeName($item['fsTYPE_NAME'])) {
                $channels[] = build_channel_item($item, $path);
            }
        }
        $groups[] = ["name" => $type, "channels" => $channels];
    }

    return ["groups" => $groups];
}

function build_channel_item($item, $path) {
    return [
        "name" => $item['fsNAME'],
        "logo" => $item['fsLOGO_MOBILE'],
        "tvg" => $item['fsNAME'],
        "seasons" => [[
            "episodes" => [[
                "links" => [[
                    "url" => "https://www.4gtv.tv/channel/" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID'],
                    "js" => $path
                ]]
            ]]
        ]]
    ];
}

/* =========================
 * 請求真實播放地址
 * ========================= */
function GetPlayUrl($url) {
    // 解析 URL 參數
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';
    $parts = explode('/', parse_url($url, PHP_URL_PATH));
    $assetId = $parts[2] ?? '';

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

    $body = json_encode([
        "fsASSET_ID" => $assetId,
        "fnCHANNEL_ID" => (int)$ch,
        "clsAPP_IDENTITY_VALIDATE_ARUS" => [
            "fsVALUE" => "",
            "fsENC_KEY" => $encKey
        ],
        "fsDEVICE_TYPE" => "mobile"
    ]);

    $ch_curl = curl_init("https://api2.4gtv.tv/App/GetChannelUrl2");
    curl_setopt($ch_curl, CURLOPT_POST, 1);
    curl_setopt($ch_curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch_curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch_curl, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch_curl);
    curl_close($ch_curl);

    $data = json_decode($resp, true)['Data'] ?? null;

    if (!$data || !isset($data['flstURLs']) || !is_array($data['flstURLs'])) {
        return ["error" => "無法取得直播源！"];
    }

    $urls = $data['flstURLs'];
    $index = rand(0, count($urls) - 1);
    return ["url" => $urls[$index] . "#" . count($urls) . "-" . ($index + 1)];
}

/* =========================
 * 4gtv_auth 生成
 * ========================= */
function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encDataB64 = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKeyB64 = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIVB64 = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = Base64toXOR($encDataB64, $xorKey);
    $key = Base64toXOR($encKeyB64, $xorKey);
    $iv = Base64toXOR($encIVB64, $xorKey);

    $today = date("Ymd");
    
    // PHP 的 AES-256-CBC
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    $hash = hash('sha512', $today . $clean);
    return base64_encode(hex2bin($hash));
}

/* =========================
 * 輔助工具函數
 * ========================= */

function GetTypeName($str) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    return implode('', array_slice($matches[0], 0, 2));
}

function BuildencKey($len) {
    if ($len <= 0) return 0;
    return rand(pow(10, $len - 1), pow(10, $len) - 1);
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $keyLen];
    }
    return $out;
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
