<?php
/**
 * 4GTV (四季線上) 頻道獲取服務 - 修正版
 * 請確保環境已安裝 php-curl 與 php-openssl
 */

class FourseasService {
    private $xorKey = "20241010-20241012";
    private $userAgent = "Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15";

    // 固定加密參數
    private $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
    private $encKey  = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
    private $encIV   = "eGV/TEdmfF1eSEFnYFR7Xw==";

    /**
     * 執行主入口 (對應原 main function)
     * @param int $mode 3: 頻道列表, 1: 播放地址
     * @param string $url 播放請求用的 URL
     */
    public function run($mode, $url = '') {
        if ($mode == 3) {
            return $this->getChannelList();
        } else if ($mode == 1) {
            return $this->getPlayUrl($url);
        }
        return ['error' => 'Invalid mode'];
    }

    /* =========================
     * 頻道列表獲取
     * ========================= */
    public function getChannelList() {
        $groups = [];
        $headers = ["User-Agent: " . $this->userAgent];

        // 1. 飛速看分組
        $resp = $this->fetch("https://4gtv.tv", $headers);
        if (isset($resp['Data'])) {
            $channels = [];
            foreach ($resp['Data'] as $item) {
                $channels[] = $this->formatChannel($item);
            }
            $groups[] = ["name" => "飛速看", "channels" => $channels];
        }

        // 2. 其他全部分類
        $resp = $this->fetch("https://4gtv.tv", $headers);
        if (isset($resp['Data'])) {
            $tempGroups = [];
            foreach ($resp['Data'] as $item) {
                $typeName = $this->getTypeName($item['fsTYPE_NAME']);
                if (!isset($tempGroups[$typeName])) {
                    $tempGroups[$typeName] = [];
                }
                $tempGroups[$typeName][] = $this->formatChannel($item);
            }
            foreach ($tempGroups as $name => $channels) {
                $groups[] = ["name" => $name, "channels" => $channels];
            }
        }

        return ["groups" => $groups];
    }

    /* =========================
     * 播放地址獲取
     * ========================= */
    public function getPlayUrl($requestUrl) {
        // 解析參數
        parse_str(parse_url($requestUrl, PHP_URL_QUERY), $query);
        $ch = $query['ch'] ?? '';
        $parts = explode('/', parse_url($requestUrl, PHP_URL_PATH));
        $assetId = end($parts);

        $auth = $this->get4gtvAuth();
        $encKeyStr = $this->buildEncKey(4) . "B" . $this->buildEncKey(3) . "-" . $this->buildEncKey(2) . "FA-45E8-8FA8-5C" . $this->buildEncKey(6) . "A" . $this->buildEncKey(3);

        $headers = [
            "Content-Type: application/json",
            "fsenc_key: $encKeyStr",
            "fsdevice: iOS",
            "4gtv_auth: $auth",
            "User-Agent: okhttp/3.12.11",
            "fsversion: 3.1.0"
        ];

        $body = [
            "fsASSET_ID" => $assetId,
            "fnCHANNEL_ID" => (int)$ch,
            "clsAPP_IDENTITY_VALIDATE_ARUS" => [
                "fsVALUE" => "",
                "fsENC_KEY" => $encKeyStr
            ],
            "fsDEVICE_TYPE" => "mobile"
        ];

        $resp = $this->fetch("https://4gtv.tv", $headers, 'POST', $body);
        
        if (isset($resp['Data']['flstURLs']) && is_array($resp['Data']['flstURLs'])) {
            $urls = $resp['Data']['flstURLs'];
            $index = array_rand($urls);
            return ["url" => $urls[$index]];
        }

        return ["error" => "無法取得播放地址，請檢查 IP 是否在台灣。"];
    }

    /* =========================
     * 核心加密邏輯 (修正重點)
     * ========================= */
    private function get4gtvAuth() {
        // 1. XOR 密文還原
        $data = $this->base64ToXor($this->encData);
        $key  = $this->base64ToXor($this->encKey);
        $iv   = $this->base64ToXor($this->encIV);

        // 2. AES 解密 (AES-256-CBC)
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $clean = rtrim($decrypted, "\0");

        // 3. SHA512 哈希
        $today = date("Ymd");
        $hash = hash("sha512", $today . $clean);

        // 4. Hex 轉 Base64
        return base64_encode(pack("H*", $hash));
    }

    private function base64ToXor($b64) {
        $raw = base64_decode($b64);
        $out = "";
        $keyLen = strlen($this->xorKey);
        for ($i = 0; $i < strlen($raw); $i++) {
            $out .= $raw[$i] ^ $this->xorKey[$i % $keyLen];
        }
        return $out;
    }

    private function getTypeName($str) {
        // PHP 處理中文正則需要 /u
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
        return implode('', array_slice($matches[0], 0, 2));
    }

    private function buildEncKey($len) {
        return str_pad(mt_rand(0, pow(10, $len) - 1), $len, '0', STR_PAD_LEFT);
    }

    private function formatChannel($item) {
        return [
            "name" => $item['fsNAME'],
            "logo" => $item['fsLOGO_MOBILE'],
            "url"  => "https://4gtv.tv" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID']
        ];
    }

    /* =========================
     * 網路請求封裝
     * ========================= */
    private function fetch($url, $headers = [], $method = 'GET', $body = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        curl_close($ch);
        return $data;
    }
}

// --- 執行範例 ---
header('Content-Type: application/json; charset=utf-8');
$service = new FourseasService();

// 如果是獲取頻道列表
$result = $service->run(3);

// 如果是獲取播放地址 (範例 URL)
// $result = $service->run(1, "https://4gtv.tv4gtv-4gtv001?set=1&ch=1");

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
