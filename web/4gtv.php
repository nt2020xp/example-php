<?php
/**
 * 4GTV PHP 直傳 TXT 格式
 * 存為 4gtv.php
 */

// 強制時區，避免 Auth 失敗
date_default_timezone_set('Asia/Taipei');

// 取得當前 PHP 的完整 URL 基礎
$selfPath = 'http://' . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0];

// 路由判斷
$mode = isset($_GET['mode']) ? (int)$_GET['mode'] : 0;

if ($mode === 1) {
    // 模式 1：返回真實播放 URL (給播放器用)
    header('Content-Type: application/json; charset=utf-8');
    $ctx = ['url' => $_GET['url'] ?? ''];
    $result = GetPlayUrl($ctx);
    
    // 提取真正的 m3u8 位址並跳轉或輸出
    if (isset($result['url'])) {
        $realUrl = explode('#', $result['url'])[0]; // 去掉 # 之後的統計字串
        header("Location: $realUrl");
    } else {
        echo "Error: Cannot get play URL";
    }
    exit;
} else {
    // 預設模式：產生 TXT 頻道清單
    header('Content-Type: text/plain; charset=utf-8');
    echo GenerateTxtList($selfPath);
}

/* =========================
 * 產生 TXT 清單邏輯
 * ========================= */
function GenerateTxtList($selfPath) {
    $headers = ["User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"];
    $output = "";

    // 1. 獲取「飛速看」分組
    $resp = curlFetch("https://4gtv.tv", $headers);
    $data = json_decode($resp, true)['Data'] ?? [];
    
    if (!empty($data)) {
        $output .= "飛速看,#genre#\n";
        foreach ($data as $item) {
            $playUrl = $selfPath . "?mode=1&url=" . urlencode("https://4gtv.tv" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID']);
            $output .= $item['fsNAME'] . "," . $playUrl . "\n";
        }
    }

    // 2. 獲取常規頻道並分組
    $resp = curlFetch("https://4gtv.tv", $headers);
    $allData = json_decode($resp, true)['Data'] ?? [];

    $typeList = [];
    foreach ($allData as $item) {
        $typeName = GetTypeName($item['fsTYPE_NAME']);
        if (!in_array($typeName, $typeList)) $typeList[] = $typeName;
    }

    foreach ($typeList as $type) {
        $output .= $type . ",#genre#\n";
        foreach ($allData as $item) {
            if ($type === GetTypeName($item['fsTYPE_NAME'])) {
                $playUrl = $selfPath . "?mode=1&url=" . urlencode("https://4gtv.tv" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID']);
                $output .= $item['fsNAME'] . "," . $playUrl . "\n";
            }
        }
    }
    return $output;
}

/* =========================
 * 核心加密與請求函式 (保持不變)
 * ========================= */
function GetPlayUrl($ctx) {
    parse_str(parse_url($ctx['url'], PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';
    $pathParts = explode('/', parse_url($ctx['url'], PHP_URL_PATH));
    $assetId = $pathParts[2] ?? ''; // 修正索引

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
        "clsAPP_IDENTITY_VALIDATE_ARUS" => ["fsVALUE" => "", "fsENC_KEY" => $encKey],
        "fsDEVICE_TYPE" => "mobile"
    ]);

    $resp = curlFetch("https://4gtv.tv", $headers, "POST", $body);
    $data = json_decode($resp, true)['Data'] ?? null;
    return (isset($data['flstURLs'][0])) ? ["url" => $data['flstURLs'][0]] : ["error" => "fail"];
}

function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKey = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIV = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = Base64toXOR($encData, $xorKey);
    $key = Base64toXOR($encKey, $xorKey);
    $iv = Base64toXOR($encIV, $xorKey);
    $today = date("Ymd");
    
    $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash('sha512', $today . rtrim($decrypted, "\0"), true);
    return base64_encode($hash);
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = ''; $kLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) { $out .= $raw[$i] ^ $key[$i % $kLen]; }
    return $out;
}

function BuildencKey($len) {
    return ($len <= 0) ? 0 : rand(pow(10, $len - 1), pow(10, $len) - 1);
}

function GetTypeName($str) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    return implode('', array_slice($matches[0], 0, 2));
}

function curlFetch($url, $headers = [], $method = "GET", $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($method === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}
?>
