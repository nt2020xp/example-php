<?php
/**
 * 4GTV JS to PHP 完整轉換版
 */

class JZ_Simulator {
    public $mode = 1; // 預設測試模式 1
    public $path = "local_js_path";

    public function getQuery($url, $key) {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);
        return $query[$key] ?? '';
    }

    public function opensslDecrypt($data, $method, $key, $options, $iv) {
        // 使用 OPENSSL_RAW_DATA 並手動處理填充問題
        return openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
    }

    public function digest($algo, $data) {
        return hash($algo, $data);
    }
}

$jz = new JZ_Simulator();

/**
 * 主入口
 */
function main($ctx) {
    global $jz;
    if ($jz->mode == 3) {
        return GetChannelList($ctx);
    } else if ($jz->mode == 1) {
        return GetPlayUrl($ctx);
    }
}

/**
 * 通用 Fetch 模擬 (使用 cURL)
 */
function my_fetch($url, $options = []) {
    $ch = curl_init($url);
    $headers = [];
    if (isset($options['headers'])) {
        foreach ($options['headers'] as $k => $v) {
            $headers[] = "$k: $v";
        }
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 避免 SSL 憑證問題
    curl_setopt($ch, CURLOPT_USERAGENT, $options['headers']['user-agent'] ?? 'Mozilla/5.0');

    if (isset($options['method']) && strtoupper($options['method']) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $postData = is_array($options['body']) ? json_encode($options['body']) : $options['body'];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    $response = curl_exec($ch);
    $data = json_decode($response);
    curl_close($ch);
    return $data;
}

/**
 * 模式 3: 頻道清單
 */
function GetChannelList($ctx) {
    global $jz;
    $api = "https://4gtv.tv";
    $headers = ["user-agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"];

    $resp = my_fetch($api, ["headers" => $headers]);
    $data = $resp->Data ?? [];

    $groups = [];
    $channels = [];

    foreach ($data as $item) {
        $channels[] = formatChannelItem($item, $jz->path);
    }
    $groups[] = ["name" => "飛速看", "channels" => $channels];

    // 取得所有分類頻道
    $api = "https://4gtv.tv";
    $resp = my_fetch($api, ["headers" => $headers]);
    $allData = $resp->Data ?? [];

    $typeList = [];
    foreach ($allData as $item) {
        $typeName = GetTypeName($item->fsTYPE_NAME);
        if (!in_array($typeName, $typeList)) {
            $typeList[] = $typeName;
        }
    }

    foreach ($typeList as $type) {
        $typeChannels = [];
        foreach ($allData as $item) {
            if ($type === GetTypeName($item->fsTYPE_NAME)) {
                $typeChannels[] = formatChannelItem($item, $jz->path);
            }
        }
        $groups[] = ["name" => $type, "channels" => $typeChannels];
    }

    return ["groups" => $groups];
}

function formatChannelItem($item, $jsPath) {
    return [
        "name" => $item->fsNAME,
        "logo" => $item->fsLOGO_MOBILE,
        "tvg" => $item->fsNAME,
        "seasons" => [[
            "episodes" => [[
                "links" => [[
                    "url" => "https://4gtv.tv" . $item->fs4GTV_ID . "?set=1&ch=" . $item->fnID,
                    "js" => $jsPath
                ]]
            ]]
        ]]
    ];
}

/**
 * 類型名稱處理 (取前兩個中文)
 */
function GetTypeName($str) {
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    $chars = $matches[0] ?? [];
    return implode('', array_slice($chars, 0, 2));
}

/**
 * 隨機數字生成
 */
function BuildencKey($len) {
    if ($len <= 0) return 0;
    $min = pow(10, $len - 1);
    $max = pow(10, $len) - 1;
    return rand($min, $max);
}

/**
 * 模式 1: 取得真實播放位址
 */
function GetPlayUrl($ctx) {
    global $jz;
    $ch = $jz->getQuery($ctx['url'], "ch");
    
    // 解析 Asset ID (從 URL 路徑取第 4 段)
    $urlPath = parse_url($ctx['url'], PHP_URL_PATH);
    $pathParts = explode('/', trim($urlPath, '/'));
    $assetId = $pathParts[1] ?? ''; // 原 JS 是 [4]，視網址格式調整

    $auth = Get4gtvauth();
    $encKey = BuildencKey(4) . "B" . BuildencKey(3) . "-" . BuildencKey(2) . "FA-45E8-8FA8-5C" . BuildencKey(6) . "A" . BuildencKey(3);

    $headers = [
        "content-type" => "application/json",
        "fsenc_key" => $encKey,
        "fsdevice" => "iOS",
        "4gtv_auth" => $auth,
        "user-agent" => "okhttp/3.12.11",
        "fsversion" => "3.1.0"
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

    $resp = my_fetch("https://4gtv.tv", [
        "headers" => $headers,
        "method" => "POST",
        "body" => $body
    ]);

    $data = $resp->Data ?? null;
    if (!$data || !isset($data->flstURLs) || !is_array($data->flstURLs)) {
        return ["error" => "無法取得直播源！內容：" . json_encode($resp)];
    }

    $urls = $data->flstURLs;
    $index = array_rand($urls);
    return ["url" => $urls[$index] . "#" . count($urls) . "-" . ($index + 1)];
}

/**
 * 身份驗證核心
 */
function Get4gtvauth() {
    global $jz;
    $xorKey = "20241010-20241012";
    $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKey = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIV = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = Base64toXOR($encData, $xorKey);
    $key = Base64toXOR($encKey, $xorKey);
    $iv = Base64toXOR($encIV, $xorKey);

    $decrypted = $jz->opensslDecrypt($data, "AES-256-CBC", $key, 0, $iv);
    $clean = rtrim($decrypted, "\x00..\x1F");

    $today = date("Ymd");
    $hash = hash("sha512", $today . $clean);
    
    // JS 的 Hex2Base64 等於 PHP 的 hex2bin -> base64_encode
    return base64_encode(hex2bin($hash));
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = "";
    $kLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $kLen];
    }
    return $out;
}

// --- 測試區 (直接執行此檔案) ---
$test_ctx = [
    "url" => "https://4gtv.tv4gtv-4gtv001?set=1&ch=1"
];

header('Content-Type: application/json; charset=utf-8');
$result = main($test_ctx);
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
