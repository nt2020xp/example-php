<?php

/**
 * 4GTV API 解析器 (PHP 轉換版) - Mode 1 獲取播放地址
 */

// --- 設定區 ---
$jz_mode = 1; // 設置為 1，獲取播放位址
$jz_path = "path/to/js"; 

// 測試用的 URL (民視新聞台範例)
$current_url = "https://4gtv.tv"; 
// --------------

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
 * 請求真實播放地址 (Mode 1)
 * ========================= */
function GetPlayUrl($url) {
    // 1. 解析頻道編號 (ch)
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';

    // 2. 解析 Asset ID (例如 4gtv-4gtv001)
    $path = parse_url($url, PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    $assetId = '';
    foreach ($pathParts as $key => $part) {
        if ($part === 'channel' && isset($pathParts[$key + 1])) {
            $assetId = $pathParts[$key + 1];
            break;
        }
    }

    if (empty($ch) || empty($assetId)) {
        return ["error" => "URL 格式不正確，需包含 /channel/xxx?ch=yyy"];
    }

    $auth = Get4gtvauth();
    // 生成動態 encKey
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

    $ch_curl = curl_init("https://4gtv.tv");
    curl_setopt($ch_curl, CURLOPT_POST, 1);
    curl_setopt($ch_curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch_curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_curl, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch_curl);
    curl_close($ch_curl);

    $json = json_decode($resp, true);
    $data = $json['Data'] ?? null;

    if (!$data || !isset($data['flstURLs']) || empty($data['flstURLs'])) {
        return [
            "error" => "無法取得直播源！",
            "api_msg" => $json['Message'] ?? '未知錯誤',
            "debug" => ["assetId" => $assetId, "ch" => $ch]
        ];
    }

    $urls = $data['flstURLs'];
    $index = rand(0, count($urls) - 1);
    return ["url" => $urls[$index]];
}

/* =========================
 * 4gtv_auth 生成與解密
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
    
    // AES-256-CBC 解密
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    $hash = hash('sha512', $today . $clean);
    return base64_encode(hex2bin($hash));
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

function BuildencKey($len) {
    if ($len <= 0) return 0;
    return rand(pow(10, $len - 1), pow(10, $len) - 1);
}

// 頻道列表相關 (Mode 3) 的其餘代碼可保留在下方...
