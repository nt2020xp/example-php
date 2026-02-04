<?php

/**
 * 模擬原程式碼中的 jz 物件環境
 * 實際使用時請根據您的環境調整這些變數來源
 */
$jz = [
    'mode' => 3, 
    'path' => 'your_js_path_here',
    'url'  => $_GET['url'] ?? ''
];

function main($ctx) {
    global $jz;
    if ($jz['mode'] == 3) {
        return GetChannelList($ctx);
    } else if ($jz['mode'] == 1) {
        $playUrl = GetPlayUrl($ctx);
        return ["url" => $playUrl];
    }
}

function GetChannelList($ctx) {
    global $jz;
    $headers = [
        "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"
    ];

    // 1. 取得飛速看分組
    $api1 = "https://api2.4gtv.tv/Channel/GetChannelFastTV";
    $data1 = json_decode(http_fetch($api1, $headers), true)['Data'];

    $groups = [];
    $channels = [];

    foreach ($data1 as $item) {
        $channels[] = format_channel_item($item, $jz['path']);
    }
    $groups[] = ["name" => "飛速看", "channels" => $channels];

    // 2. 取得其他分組
    $api2 = "https://api2.4gtv.tv/Channel/GetChannelBySetId/1/pc/L";
    $data2 = json_decode(http_fetch($api2, $headers), true)['Data'];

    $typeList = [];
    foreach ($data2 as $item) {
        $typeName = GetTypeName($item['fsTYPE_NAME']);
        if (!in_array($typeName, $typeList)) {
            $typeList[] = $typeName;
        }
    }

    foreach ($typeList as $type) {
        $typeChannels = [];
        foreach ($data2 as $item) {
            if ($type === GetTypeName($item['fsTYPE_NAME'])) {
                $typeChannels[] = format_channel_item($item, $jz['path']);
            }
        }
        $groups[] = ["name" => $type, "channels" => $typeChannels];
    }

    return ["groups" => $groups];
}

function format_channel_item($item, $jsPath) {
    return [
        "name" => $item['fsNAME'],
        "logo" => $item['fsLOGO_MOBILE'],
        "tvg"  => $item['fsNAME'],
        "seasons" => [[
            "episodes" => [[
                "links" => [[
                    "url" => "https://www.4gtv.tv/channel/{$item['fs4GTV_ID']}?set=1&ch={$item['fnID']}",
                    "js"  => $jsPath
                ]]
            ]]
        ]]
    ];
}

function GetTypeName($str) {
    // 匹配前兩個中文字符
    preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
    return implode('', array_slice($matches[0], 0, 2));
}

function BuildencKey($len) {
    if ($len <= 0) return 0;
    $min = pow(10, $len - 1);
    $max = pow(10, $len) - 1;
    return rand($min, $max);
}

function GetPlayUrl($ctx) {
    global $jz;
    // 取得 URL 參數與路徑中的 ID
    parse_str(parse_url($ctx['url'], PHP_URL_QUERY), $query);
    $ch = $query['ch'] ?? '';
    $pathParts = explode('/', explode('?', $ctx['url'])[0]);
    $assetId = $pathParts[4] ?? '';

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
        "fnCHANNEL_ID" => $ch,
        "clsAPP_IDENTITY_VALIDATE_ARUS" => [
            "fsVALUE" => "",
            "fsENC_KEY" => $encKey
        ],
        "fsDEVICE_TYPE" => "mobile"
    ]);

    $respJson = http_fetch("https://api2.4gtv.tv/App/GetChannelUrl2", $headers, "POST", $body);
    $data = json_decode($respJson, true)['Data'];

    if (!$data || !isset($data['flstURLs']) || !is_array($data['flstURLs'])) {
        return ["error" => "無法取得直播源！"];
    }

    $urls = $data['flstURLs'];
    $index = rand(0, count($urls) - 1);
    return $urls[$index] . "#" . count($urls) . "-" . ($index + 1);
}

function Get4gtvauth() {
    $xorKey = "20241010-20241012";
    $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    $encKey  = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    $encIV   = "eGV/TEdmfF1eSEFnYFR7Xw==";

    $data = Base64toXOR($encData, $xorKey);
    $key  = Base64toXOR($encKey, $xorKey);
    $iv   = Base64toXOR($encIV, $xorKey);

    $today = date("Ymd");

    // 使用 PHP 標準 openssl 進行解密
    $decrypted = openssl_decrypt($data, "AES-256-CBC", $key, OPENSSL_RAW_DATA, $iv);
    $clean = rtrim($decrypted, "\0");

    // SHA512 後轉為 Base64
    $hash = hash("sha512", $today . $clean);
    return base64_encode(hex2bin($hash));
}

function Base64toXOR($b64, $key) {
    $raw = base64_decode($b64);
    $out = "";
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($raw); $i++) {
        $out .= $raw[$i] ^ $key[$i % $keyLen];
    }
    return $out;
}

/**
 * 輔助函式：處理 HTTP 請求
 */
function http_fetch($url, $headers = [], $method = "GET", $body = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($method === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

?>
