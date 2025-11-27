<?php
// ===================================
// 核心 cURL 函數定義
// ===================================

/**
 * 通用 cURL 函數，用於發送 HTTP 請求並返回內容。
 * @param string $url 請求的 URL
 * @param array $header HTTP 請求標頭
 * @return string|false 請求返回的內容，失敗時返回 false
 */
function curl($url, $header = array()){
    $ch = curl_init();
    
    // 設置 URL 和基本選項
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 總是返回內容作為字符串
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟隨重定向
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // 設置超時時間 (秒)

    // SSL/TLS 選項 (常用於繞過證書問題，但生產環境中應避免)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 設置 HTTP 標頭
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    
    $result = curl_exec($ch);
    
    // 檢查 cURL 錯誤
    if (curl_errno($ch)) {
        // 您可以在此處添加錯誤日誌記錄
        $result = false; 
    }
    
    curl_close($ch);
    return $result;
}

// ===================================
// 輔助函數
// ===================================

/**
 * 獲取當前請求的協議方案 (http 或 https)，優先考慮 Cloudflare 或轉發協議。
 * @return string
 */
function get_current_scheme(){
    $pro = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if(empty($pro)){
        // 嘗試解析 Cloudflare 訪問者標頭
        $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR'] ?? '{}', true);
        $pro = $cf_visitor["scheme"] ?? 'http';
    }
    // 確保返回小寫
    return strtolower($pro);
}

// ===================================
// 全局配置和參數獲取
// ===================================

// 在測試環境中，為了方便調試，您可以暫時註釋掉 error_reporting(0);
error_reporting(0); 

// 從 GET 參數獲取 m3u8, ts, uid, token
$m3u8 = $_GET["m3u8"] ?? '';
$ts = $_GET["ts"] ?? '';
$uid = $_GET["uid"] ?? ''; 
$token = $_GET["token"] ?? '';

// ===================================
// 模式一：返回 M3U8 播放列表 (初始請求)
// ===================================
if(empty($m3u8) && empty($ts)){
    // 設置 Content-Type 為 text/plain 適用於列表輸出
    header('Content-Type: text/plain;charset=UTF-8',true,200);

    // 1. 獲取 Token 資訊 (用於觸發遠端更新，雖然返回結果沒有被使用)
    $init_data = curl("http://cookies.elementfx.com/superb/superb.php"); 

    // 2. 獲取列表數據
    $data = curl("http://cookies.elementfx.com/superb/superb.php?list=1");
    
    if ($data === false) {
        die("Error: Failed to fetch data from remote server.");
    }

    // 3. 解碼和解壓
    $data_decoded = base64_decode($data);
    if ($data_decoded === false) {
        die("Error: Base64 decode failed.");
    }
    
    // 由於 gzuncompress 可能失敗，增加檢查
    $data_uncompressed = @gzuncompress($data_decoded);
    if ($data_uncompressed === false) {
        die("Error: Gzip uncompress failed. Check zlib extension.");
    }
    
    $data_obj = json_decode($data_uncompressed, false); // false for objects

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data_obj)) {
        die("Error: Failed to decode JSON list data.");
    }
    
    $pro = get_current_scheme();
    // 使用 REQUEST_URI 確保包含所有原始查詢參數，但我們在這裡只用來構建基礎 URL
    $self_base = $pro . "://" . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0];
    
    $result = "";
    foreach($data_obj as $item){
        if(isset($item->url) && substr($item->url, 0, 1) == "/"){
            $category = str_replace("/", "", $item->category ?? 'Uncategorized');
            $name = $item->name ?? 'Unknown';
            // 構建結果格式：類別_名稱,完整代理URL
            $result .= "{$category}_{$name},{$self_base}?m3u8={$item->url}\n";
        }
    }
    echo $result;
}

// ===================================
// 模式二：代理 M3U8 文件
// ===================================
if(!empty($m3u8) && empty($ts)){
    // 1. 檢查並更新 Token (每 5 分鐘更新一次)
    $token_file_exists = file_exists("./token.txt");
    $need_update = !$token_file_exists || (time() - filemtime("./token.txt") > 300);

    if($need_update){
        $info_json = curl("http://cookies.elementfx.com/superb/superb.php");
        $info = json_decode($info_json);

        if ($info && isset($info->host, $info->uid, $info->token)) {
            // 確保檔案寫入成功，如果失敗可能是權限問題
            file_put_contents("./host.txt", $info->host);
            file_put_contents("./uid.txt", $info->uid);
            file_put_contents("./token.txt", $info->token);
        } else {
             // 遠端 API 獲取失敗，可以使用本地舊 token (如果存在)
             if (!$token_file_exists) {
                die("Error: Failed to fetch initial token information.");
             }
        }
    }
    
    // 2. 從本地文件讀取最新的 host/uid/token
    $host = file_get_contents("./host.txt") ?: '';
    $uid = file_get_contents("./uid.txt") ?: '';
    $token = file_get_contents("./token.txt") ?: '';

    $url = $host.$m3u8;
    $url_parts = explode("/index", $url);
    $pre = $url_parts[0]; // 獲取 TS 檔案的前綴 URL

    // 3. 請求 M3U8 文件
    $header = array(
        "User-Agent: Lavf/58.12.100", // 保持用戶代理以模擬客戶端
        "Accept: */*",
        "Connection: keep-alive",
        "Icy-MetaData: 1",
        "userid: {$uid}",
        "usertoken: {$token}",
        "Cache-Control: no-cache",
        "Pragma: no-cache"
    );
    $data = curl($url, $header); 
    
    if ($data === false) {
         http_response_code(500); 
         die("#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-ENDLIST\n# Request Failed.");
    }

    // 4. 解析並重寫 TS 連結
    $data_lines = explode("\n", $data);
    $pro = get_current_scheme();
    $self_script_name = $pro."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; // 腳本本身的 URL

    $output_lines = [];
    foreach($data_lines as $line){
        $line = trim($line);
        if(empty($line)){
            continue;
        }

        if(substr($line, 0, 1) !== "#"){
            // 這是一個 TS 文件路徑，重寫為指向本腳本的代理連結
            $new_line = "{$self_script_name}?ts={$pre}/{$line}&uid={$uid}&token={$token}";
            $output_lines[] = $new_line;
        } else {
            // 保持 M3U8 標頭行不變
            $output_lines[] = $line;
        }
    }
    
    // 5. 輸出重寫後的 M3U8 文件
    header('Content-Type: application/vnd.apple.mpegurl;charset=UTF-8', true, 200); 
    echo implode("\n", $output_lines);
}

// ===================================
// 模式三：代理 TS 文件
// ===================================
if(empty($m3u8) && !empty($ts)){
    // 設置 TS 文件請求標頭
    $header = array(
        "
