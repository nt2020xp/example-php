<?php
/**
 * 4GTV (四季線上) 頻道獲取 PHP 版 (修正版)
 */

class FourseasTV {
    private $userAgent = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15";
    private $xorKey = "20241010-20241012";

    // 加密組件 (參考原 JavaScript 邏輯)
    private $encDataB64 = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    private $encKeyB64 = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    private $encIvB64 = "eGV/TEdmfF1eSEFnYFR7Xw==";

    public function getChannelList() {
        $groups = [];
        $headers = ["User-Agent: " . $this->userAgent];

        // 1. 取得「飛速看」
        $fastData = $this->httpGet("https://4gtv.tv", $headers);
        if (isset($fastData['Data'])) {
            $channels = [];
            foreach ($fastData['Data'] as $item) $channels[] = $this->formatChannelItem($item);
            $groups[] = ["name" => "飛速看", "channels" => $channels];
        }

        // 2. 取得全部分類
        $setData = $this->httpGet("https://4gtv.tv", $headers);
        if (isset($setData['Data'])) {
            $typeMap = [];
            foreach ($setData['Data'] as $item) {
                preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $item['fsTYPE_NAME'], $matches);
                $typeName = implode('', array_slice($matches[0], 0, 2));
                $typeMap[$typeName][] = $this->formatChannelItem($item);
            }
            foreach ($typeMap as $name => $channels) $groups[] = ["name" => $name, "channels" => $channels];
        }
        return ["groups" => $groups];
    }

    public function getPlayUrl($url) {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $ch = $query['ch'] ?? '';
        $pathParts = explode('/', parse_url($url, PHP_URL_PATH));
        $assetId = $pathParts[count($pathParts) - 1] ?? '';

        $auth = $this->generate4gtvAuth();
        $encKeyStr = $this->buildEncKey();

        $postData = [
            "fsASSET_ID" => $assetId,
            "fnCHANNEL_ID" => (int)$ch,
            "clsAPP_IDENTITY_VALIDATE_ARUS" => ["fsVALUE" => "", "fsENC_KEY" => $encKeyStr],
            "fsDEVICE_TYPE" => "mobile"
        ];

        $headers = [
            "Content-Type: application/json",
            "fsenc_key: " . $encKeyStr,
            "fsdevice: iOS",
            "4gtv_auth: " . $auth,
            "User-Agent: okhttp/3.12.11",
            "fsversion: 3.1.0"
        ];

        $resp = $this->httpPost("https://4gtv.tv", $postData, $headers);
        return $resp['Data']['flstURLs'][0] ?? ["error" => "無法取得直播源！"];
    }

    // --- 修正核心邏輯 ---
    private function generate4gtvAuth() {
        $decrypted = openssl_decrypt($this->xorDecrypt($this->encDataB64), 'AES-256-CBC', $this->xorDecrypt($this->encKeyB64), OPENSSL_RAW_DATA, $this->xorDecrypt($this->encIvB64));
        return base64_encode(hex2bin(hash("sha512", date("Ymd") . rtrim($decrypted, "\0"))));
    }

    private function xorDecrypt($b64) {
        $raw = base64_decode($b64);
        $out = "";
        for ($i = 0; $i < strlen($raw); $i++) $out .= $raw[$i] ^ $this->xorKey[$i % strlen($this->xorKey)];
        return $out;
    }

    private function buildEncKey() {
        $r = function($l) { return str_pad(rand(0, pow(10, $l) - 1), $l, '0', STR_PAD_LEFT); };
        return $r(4)."B".$r(3)."-".$r(2)."FA-45E8-8FA8-5C".$r(6)."A".$r(3);
    }

    private function formatChannelItem($item) {
        return ["name" => $item['fsNAME'], "logo" => $item['fsLOGO_MOBILE'], "url" => "https://4gtv.tv" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID']];
    }

    private function httpGet($url, $headers) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }

    private function httpPost($url, $data, $headers) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);
    }
}

// 使用範例
$tv = new FourseasTV();
$result = $tv->getChannelList();
header('Content-Type: application/json');
echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>
