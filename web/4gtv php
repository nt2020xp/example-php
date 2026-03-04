<?php

/**
 * 模擬 jz 環境變數
 */
$jz = [
    'mode' => 1,
    'path' => 'custom_path'
];

/**
 * 核心執行入口
 */
function main($ctx_url) {
    global $jz;
    if ($jz['mode'] == 3) {
        return getChannelList();
    } else if ($jz['mode'] == 1) {
        return getPlayUrl($ctx_url);
    }
}

/**
 * 4gtv_auth 生成邏輯 (AES-256-CBC + SHA512)
 */
function get4gtvAuth() {
    $xorKey = "20241010-20241012";
    $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKey = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIV = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = base64ToXor($encData, $xorKey);
    $key = base64ToXor($encKey, $xorKey);
    $iv = base64ToXor($encIV, $xorKey);

    // AES 解密 (OPENSSL_RAW_DATA 確保輸出原始二進制)
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    $today = date("Ymd");
    // SHA512 後轉為 Base64 (需先轉二進制)
    $hash = hash('sha512', $today . $clean);
    return base64_encode(hex2bin($hash));
}

/**
 * Base64 解碼後與 Key 進行 XOR
 */
function base64ToXor($b64, $key) {
    $raw = base64_decode($b64);
    $out = "";
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $keyLen];
    }
    return $out;
}

/**
 * 隨機數字生成
 */
function buildEncKey($len) {
    if ($len <= 0) return "0";
    $min = pow(10, $len - 1);
    $max = pow(10, $len) - 1;
    return (string)mt_rand($min, $max);
}

/**
 * 獲取播放地址
 */
function getPlayUrl($url) {
    // 取得 ch 參數
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';
    
    // 取得 AssetId
    $pathParts = explode('/', parse_url($url, PHP_URL_PATH));
    $assetId = end($pathParts);

    $auth = get4gtvAuth();
    $encKey = buildEncKey(4) . "B" . buildEncKey(3) . "-" . buildEncKey(2) . "FA-45E8-8FA8-5C" . buildEncKey(6) . "A" . buildEncKey(3);

    $headers = [
        "Content-Type: application/json",
        "fsenc_key: $encKey",
        "fsdevice: iOS",
        "4gtv_auth: $auth",
        "User-Agent: okhttp/3.12.11",
        "fsversion: 3.1.0"
    ];

    $body = [
        "fsASSET_ID" => $assetId,
        "fnCHANNEL_ID" => $ch,
        "clsAPP_IDENTITY_VALIDATE_ARUS" => [
            "fsVALUE" => "",
            "fsENC_KEY" => $encKey
        ],
        "fsDEVICE_TYPE" => "mobile"
    ];

    $ch_curl = curl_init("https://api2.4gtv.tv");
    curl_setopt($ch_curl, CURLOPT_POST, 1);
    curl_setopt($ch_curl, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch_curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch_curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch_curl);
    curl_close($ch_curl);
    
    $resData = json_decode($response, true);
    $urls = $resData['Data']['flstURLs'] ?? [];

    if (empty($urls)) {
        return ["error" => "無法取得直播源！"];
    }

    $index = mt_rand(0, count($urls) - 1);
    return $urls[$index] . "#" . count($urls) . "-" . ($index + 1);
}

// 測試執行
$test_url = "https://www.4gtv.tv";
echo print_r(main($test_url), true);