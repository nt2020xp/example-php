<?php

class SmtSpider {
    private $headers = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ];

    public function getName() {
        return "SMT";
    }

    /**
     * 解密並獲取頻道列表
     */
    public function getChannelList($base64Data) {
        // 對應 Python 的 self.d 的解密
        $jsonStr = base64_decode($base64Data);
        return json_decode($jsonStr, true);
    }

    /**
     * 獲取播放連結 (302 跳轉邏輯)
     */
    public function getPlayUrl($url, $pid) {
        $t = time();
        // 對應 Python 的 hashlib.md5 邏輯
        $salt = "tvata nginx auth module/{$pid}/playlist.m3u8mc42afe745533{$t}";
        $tsum = md5($salt);

        $params = [
            'pid' => $pid,
            'ct' => $t,
            'tsum' => $tsum
        ];

        $playUrl = $url . '?' . http_build_query($params);
        $encodedUrl = base64_encode($playUrl);
        
        // 返回跳轉位址
        return "http://127.0.0.1:9978/proxy?do=py&type=m3u8&url=" . $encodedUrl;
    }

    /**
     * 處理 M3U8 文本內容
     */
    public function getM3u8Text($encodedUrl) {
        $url = base64_decode($encodedUrl);
        $homeUrl = substr($url, 0, strrpos($url, '/') + 1);

        // 發起請求 (這裡使用 file_get_contents 或 curl)
        $opts = [
            "http" => ["header" => "User-Agent: " . $this->headers['User-Agent']]
        ];
        $context = stream_context_create($opts);
        $m3u8_text = file_get_contents($url, false, $context);

        // 正則替換 TS 鏈接
        $callback = function($matches) use ($homeUrl) {
            $uri = $homeUrl . trim($matches[0]);
            $base64Uri = base64_encode($uri);
            return "http://127.0.0.1:9978/proxy?do=py&type=ts&url=" . $base64Uri;
        };
