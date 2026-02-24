";
$ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36";

// 模式 A：如果帶有 id，則抓取並導向直播源
if (isset($_GET['id'])) {
    $fs_id = $_GET['id'];
    $api_url = "https://www.4gtv.tv" . $fs_id;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    $response = curl_exec($ch);
    $data = json_decode($response, true);
    curl_close($ch);

    if (isset($data['Data']['url'])) {
        header("Location: " . $data['Data']['url']);
    } else {
        header("HTTP/1.1 404 Not Found");
        echo "無法獲取直播源，請確認頻道 ID 是否正確。";
    }
    exit;
}

// 模式 B：預設輸出 M3U 播放列表
header('Content-Type: application/vnd.apple.mpegurl');
header('Content-Disposition: attachment; filename="4gtv_list.m3u"');

// 抓取全頻道清單 (Set 1 為主要直播組)
$list_api = "https://www.4gtv.tv";
$ch = curl_init($list_api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
curl_setopt($ch, CURLOPT_REFERER, $referer);
$list_json = curl_exec($ch);
$list_data = json_decode($list_json, true);
curl_close($ch);

echo "#EXTM3U\n";

if (isset($list_data['Data'])) {
    foreach ($list_data['Data'] as $channel) {
        $name = $channel['channel_name'];
        $id = $channel['fs_id'];
        $logo = $channel['img_path'] ?? '';
        $category = $channel['category_name'] ?? '其他';
        
        // 格式化 M3U 標籤
        echo "#EXTINF:-1 tvg-id=\"$id\" tvg-logo=\"$logo\" group-title=\"$category\",$name\n";
        echo "$base_url?id=$id\n";
    }
}
?>
