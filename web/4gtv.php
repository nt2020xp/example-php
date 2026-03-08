<?php

class FourGTV {
    // 模擬原腳本的 jz 對象屬性
    public $mode;
    public $path;

    public function __construct($mode = 3, $path = "") {
        $this->mode = $mode;
        $this->path = $path;
    }

    public function main($ctx) {
        if ($this->mode == 3) {
            return $this->getChannelList($ctx);
        } else if ($this->mode == 1) {
            $playUrl = $this->getPlayUrl($ctx);
            return ['url' => $playUrl];
        }
    }

    private function getChannelList($ctx) {
        $headers = [
            "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15"
        ];

        // 1. 取得「飛速看」分組
        $api1 = "https://api2.4gtv.tv/Channel/GetChannelFastTV";
        $data1 = $this->fetchJson($api1, $headers);
        
        $groups = [];
        $channels = [];
        foreach ($data1['Data'] as $item) {
            $channels[] = $this->formatChannelItem($item);
        }
        $groups[] = ['name' => '飛速看', 'channels' => $channels];

        // 2. 取得全部分組
        $api2 = "https://api2.4gtv.tv/Channel/GetChannelBySetId/1/pc/L";
        $data2 = $this->fetchJson($api2, $headers);
        $allData = $data2['Data'];

        $typeList = [];
        foreach ($allData as $item) {
            $typeName = $this->getTypeName($item['fsTYPE_NAME']);
            if (!in_array($typeName, $typeList)) {
                $typeList[] = $typeName;
            }
        }

        foreach ($typeList as $type) {
            $typeChannels = [];
            foreach ($allData as $item) {
                if ($type === $this->getTypeName($item['fsTYPE_NAME'])) {
                    $typeChannels[] = $this->formatChannelItem($item);
                }
            }
            $groups[] = ['name' => $type, 'channels' => $typeChannels];
        }

        return ['groups' => $groups];
    }

    private function formatChannelItem($item) {
        return [
            'name' => $item['fsNAME'],
            'logo' => $item['fsLOGO_MOBILE'],
            'tvg'  => $item['fsNAME'],
            'seasons' => [[
                'episodes' => [[
                    'links' => [[
                        'url' => "https://www.4gtv.tv/channel/{$item['fs4GTV_ID']}?set=1&ch={$item['fnID']}",
                        'js'  => $this->path
                    ]]
                ]]
            ]]
        ];
    }

    private function getTypeName($str) {
        preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches);
        return implode('', array_slice($matches[0], 0, 2));
    }

    private function buildEncKey($len) {
        if ($len <= 0) return 0;
        $min = pow(10, $len - 1);
        $max = pow(10, $len) - 1;
        return mt_rand($min, $max);
    }

    public function getPlayUrl($ctx) {
        // 解析 URL 參數
        parse_str(parse_url($ctx['url'], PHP_URL_QUERY), $query);
        $ch = $query['ch'] ?? '';
        $parts = explode('/', parse_url($ctx['url'], PHP_URL_PATH));
        $assetId = $parts[2] ?? ''; 

        $auth = $this->get4gtvAuth();
        $encKey = $this->buildEncKey(4) . "B" . $this->buildEncKey(3) . "-" . 
                  $this->buildEncKey(2) . "FA-45E8-8FA8-5C" . 
                  $this->buildEncKey(6) . "A" . $this->buildEncKey(3);

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

        $resp = $this->fetchJson("https://api2.4gtv.tv/App/GetChannelUrl2", $headers, 'POST', $body);
        $data = $resp['Data'] ?? null;

        if (!$data || empty($data['flstURLs'])) {
            return ["error" => "無法取得直播源！"];
        }

        $urls = $data['flstURLs'];
        $index = mt_rand(0, count($urls) - 1);
        return $urls[$index] . "#" . count($urls) . "-" . ($index + 1);
    }

    private function get4gtvAuth() {
        $xorKey = "20241010-20241012";
        $encData = "YklifmQCBFlkAHljd3xnQAVZUl5DWQlCd25LQENHSX1BBkF7WH5eCQRjZgYDWgQJVlcZWAFcVmZcWGRUYWNwH38GBnBcaEBtRwl1Vlp5G0dRBEdmWVUNDw==";
        $encKey = "W1xLdgMJa1RfR0VjXnIEBHhacnBmBl8DahVlegACZ1c=";
        $encIV = "eGV/TEdmfF1eSEFnYFR7Xw==";

        $data = $this->base64ToXor($encData, $xorKey);
        $key = $this->base64ToXor($encKey, $xorKey);
        $iv = $this->base64ToXor($encIV, $xorKey);

        $today = date("Ymd");
        
        // AES-256-CBC 解密
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $clean = rtrim($decrypted, "\0");

        // SHA512 並轉為 Base64
        $hash = hash('sha512', $today . $clean);
        return base64_encode(hex2bin($hash));
    }

    private function base64ToXor($b64, $key) {
        $raw = base64_decode($b64);
        $out = "";
        $keyLen = strlen($key);
        for ($i = 0; $i < strlen($raw); $i++) {
            $out .= $raw[$i] ^ $key[$i % $keyLen];
        }
        return $out;
    }

    private function fetchJson($url, $headers = [], $method = 'GET', $body = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }
}
