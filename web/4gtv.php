<?php
/**
 * 4GTV API 解析器 (PHP 轉換版)
 */

// --- 設定區 ---
$jz_mode = 1; // 1 為獲取播放地址, 3 為獲取列表
$jz_path = "path/to/js"; 

// 測試 URL (以民視新聞為例，格式必須包含 /channel/xxx?ch=yyy)
$current_url = "https://4gtv.tv"; 

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

/**
 * 請求真實播放地址 (Mode 1)
 */
function GetPlayUrl($url) {
    // 1. 解析 URL 參數
    parse_str(parse_url($url, PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';

    // 2. 解析 Asset ID (路徑中的 channel 名稱)
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
        return ["error" => "URL 解析失敗，請檢查格式是否包含 /channel/xxx?ch=yyy"];
    }

    // 3. 生成 4gtv_auth
    $auth = Get4gtvauth();

    // 4. 生成動態 encKey (與 JS 邏輯一致)
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

    // 5. 發送 POST 請求
    $ch_curl = curl_init("https://4gtv.tv");
    curl_setopt($ch_curl, CURLOPT_POST, 1);
    curl_setopt($ch_curl, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch_curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch_curl, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch_curl);
    curl_close($ch_curl);

    $json = json_decode($resp, true);
    $data = $json['Data'] ?? null;

    if (!$data || !isset($data['flstURLs']) || empty($data['flstURLs'])) {
        return [
            "status" => "error",
            "message" => "無法取得直播源！",
            "api_response" => $json['Message'] ?? 'API 無回應',
            "debug_info" => [
                "assetId" => $assetId,
                "ch" => $ch,
                "auth_used" => substr($auth, 0, 10) . "..." // 僅顯示部分用於核對
            ]
        ];
    }

    $urls = $data['flstURLs'];
    $index = rand(0, count($urls) - 1);
    return ["url" => $urls[$index]];
}

/**
 * 4gtv_auth 生成（AES 解密 + SHA512）
 */
function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encDataB64 = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKeyB64 = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIVB64 = "eGV/TEdmfF1eSEFnYFR7Xw==";

    // XOR 處理
    $data = Base64toXOR($encDataB64, $xorKey);
    $key = Base64toXOR($encKeyB64, $xorKey);
    $iv = Base64toXOR($encIVB64, $xorKey);

    $today = date("Ymd");
    
    // AES-256-CBC 解密 (OPENSSL_RAW_DATA 對應 JS 的 0 Padding)
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    // SHA512 哈希
    $hash = hash('sha512', $today . $clean);
    return base64_encode(hex2bin($hash));
}

/**
 * 輔助工具：Base64 轉 XOR
 */
function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $keyLen];
    }
    return $out;
}

/**
 * 輔助工具：生成隨機數字
 */
function BuildencKey($len) {
    if ($len <= 0) return 0;
    $min = pow(10, $len - 1);
    $max = pow(10, $len) - 1;
    return mt_rand($min, $max);
}

// 獲取類型名稱
function GetTypeName($str) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    $chars = $matches[0];
    return implode('', array_slice($chars, 0, 2));
}

// HTTP GET 封裝
function http_get($url, $headers) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
