<?php
class FourseasService {
    private $xorKey = "20241010-20241012";
    private $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

    // 核心 API 網址
    private $apiList = "https://4gtv.tv";
    private $apiFast = "https://4gtv.tv";

    public function getChannelList() {
        $groups = [];
        
        // 請求 Headers 必須包含 Referer 否則可能回傳空值
        $headers = [
            "User-Agent: " . $this->userAgent,
            "Referer: https://4gtv.tv",
            "Origin: https://4gtv.tv"
        ];

        // 1. 先抓取全部分類 (SetId/1 是主要清單)
        $resp = $this->fetch($this->apiList, $headers);
        
        if (!empty($resp['Data']) && is_array($resp['Data'])) {
            $tempGroups = [];
            foreach ($resp['Data'] as $item) {
                $typeName = $this->getTypeName($item['fsTYPE_NAME']);
                if (empty($typeName)) $typeName = "其他";
                
                $tempGroups[$typeName][] = $this->formatChannel($item);
            }
            foreach ($tempGroups as $name => $channels) {
                $groups[] = ["name" => $name, "channels" => $channels];
            }
        }

        // 2. 抓取「飛速看」 (如果有的話)
        $fastResp = $this->fetch($this->apiFast, $headers);
        if (!empty($fastResp['Data'])) {
            $fastChannels = [];
            foreach ($fastResp['Data'] as $item) {
                $fastChannels[] = $this->formatChannel($item);
            }
            // 插入到最前面
            array_unshift($groups, ["name" => "飛速看", "channels" => $fastChannels]);
        }

        return ["groups" => $groups];
    }

    private function getTypeName($str) {
        // 修正正則表達式，確保能抓到分類前兩個字
        if (preg_match_all('/[\x{4e00}-\x{9fa5}]/u', $str, $matches)) {
            return implode('', array_slice($matches[0], 0, 2));
        }
        return "頻道";
    }

    private function formatChannel($item) {
        return [
            "name" => $item['fsNAME'],
            "logo" => $item['fsLOGO_MOBILE'] ?? $item['fsLOGO_PC'],
            "url"  => "https://4gtv.tvchannel/" . $item['fs4GTV_ID'] . "?set=1&ch=" . $item['fnID']
        ];
    }

    private function fetch($url, $headers) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        // 如果是在海外伺服器，請取消下面註釋並填寫台灣 Proxy
        // curl_setopt($ch, CURLOPT_PROXY, "你的台灣代理IP:端口"); 
        
        $response = curl_exec($ch);
        if(curl_errno($ch)) return []; // 網絡錯誤回傳空
        
        $data = json_decode($response, true);
        curl_close($ch);
        return $data;
    }
}

// 執行並輸出
header('Content-Type: application/json; charset=utf-8');
$service = new FourseasService();
echo json_encode($service->getChannelList(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
