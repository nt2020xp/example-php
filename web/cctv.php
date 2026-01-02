<?php

class CCTVNews {
    private $app_key = "20000009";
    private $sign_key = "emasgatewayh5";

    /**
     * 主進入點：獲取直播 URL
     * @param string $id 頻道 ID (如 cctv1, cctv13)
     */
    public function getLiveUrl($id = 'cctv1') {
        $channelMap = [
            'cctv1' => '11200132825562653886', // 建議依據前次建議更新此 ID
            'cctv2' => '12030532124776958103',
            'cctv4' => '10620168294224708952',
            'cctv7' => '8516529981177953694',
            'cctv9' => '7252237247689203957',
            'cctv10' => '14589146016461298119',
            'cctv12' => '13180385922471124325',
            'cctv13' => '16265686808730585228',
            'cctv17' => '4496917190172866934',
            'cctv4k' => '2127841942201075403',
        ];

        if (!isset($channelMap[$id])) {
            return ['success' => false, 'error' => "不支持的頻道ID: $id"];
        }

        $articleId = $channelMap[$id];
        $t = time();

        // 1. 生成簽名 (Sign)
        $sail = md5("articleId={$articleId}&scene_type=6");
        $w = "&&&{$this->app_key}&{$sail}&{$t}&emas.feed.article.live.detail&1.0.0&&&&&";
        $sign = hash_hmac('sha256', $w, $this->sign_key);

        // 2. 準備請求
        $url = "https://emas-api.cctvnews.cctv.com/h5/emas.feed.article.live.detail/1.0.0?articleId={$articleId}&scene_type=6";
        $headers = [
            "cookieuid: " . md5((string)$t),
            "from-client: h5",
            "referer: https://m-live.cctvnews.cctv.com/",
            "x-emas-gw-appkey: {$this->app_key}",
            "x-emas-gw-pv: 6.1",
            "x-emas-gw-sign: $sign",
            "x-emas-gw-t: $t",
            "x-req-ts: " . ($t * 1000)
        ];

        // 3. 發送請求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        if (!$res) return ['success' => false, 'error' => '網絡請求失敗'];

        // 4. 解析數據
        $responseData = json_decode($res, true);
        if (!isset($responseData['response'])) return ['success' => false, 'error' => 'API返回格式錯誤'];

        // 解碼 Base64 響應
        $decodedData = json_decode(base64_decode($responseData['response']), true);
        $data = $decodedData['data'] ?? null;

        if (!$data) return ['success' => false, 'error' => '數據解碼失敗'];

        // 5. 提取認證 URL 與解密 Key (DK)
        $authUrl = $data['live_room']['liveCameraList'][0]['pullUrlList'][0]['authResultUrl'][0]['authUrl'] ?? null;
        $dk = $data['dk'] ?? null;

        if (!$authUrl || !$dk) {
            return ['success' => false, 'error' => '無法獲取直播地址或密鑰'];
        }

        // 6. AES 解密 (AES-128-CBC)
        $key = substr($dk, 0, 8) . substr((string)$t, -8);
        $iv = substr($dk, -8) . substr((string)$t, 0, 8);
        
        $decryptedUrl = openssl_decrypt(
            $authUrl, 
            'AES-128-CBC', 
            $key, 
            0, 
            $iv
        );

        return [
            'success' => true,
            'url' => $decryptedUrl
        ];
    }
}

// 使用範例
$api = new CCTVNews();
$result = $api->getLiveUrl('cctv1');

header('Content-Type: application/json');
echo json_encode($result);
