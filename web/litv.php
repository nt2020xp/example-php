<?php
/**
 * LiTV 全頻道直播選台器 (PHP 版)
 * 功能：自動抓取官網頻道清單，並使用 iframe 播放
 */

// 1. 抓取 LiTV 頻道列表頁面
$target_url = "https://litv.tv";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
$html = curl_exec($ch);
curl_close($ch);

// 2. 解析頻道名稱與 ID (使用正規表示法)
// 尋找 href="/channel/watch/..." 的連結
preg_match_all('/href="\/channel\/watch\/([^"]+)"[^>]*>(.*?)<\/a>/s', $html, $matches);

$channels = [];
if (!empty($matches[1])) {
    foreach ($matches[1] as $index => $id) {
        // 清理名稱中的 HTML 標籤
        $name = strip_tags($matches[2][$index]);
        $name = trim(preg_replace('/\s+/', ' ', $name));
        
        if (!empty($name) && !isset($channels[$id])) {
            $channels[$id] = $name;
        }
    }
}

// 獲取目前要播放的頻道 ID (預設為三立新聞 447)
$current_id = isset($_GET['id']) ? $_GET['id'] : '447';
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>PHP LiTV 電視直播牆</title>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: #fff; display: flex; margin: 0; height: 100vh; }
        .sidebar { width: 300px; overflow-y: auto; background: #222; border-right: 1px solid #444; padding: 10px; }
        .main { flex: 1; display: flex; flex-direction: column; }
        .player-container { flex: 1; background: #000; position: relative; }
        iframe { width: 100%; height: 100%; border: none; }
        .channel-item { 
            padding: 10px; border-bottom: 1px solid #333; cursor: pointer; color: #ccc; text-decoration: none; display: block;
        }
        .channel-item:hover { background: #333; color: #fff; }
        .active { background: #e91e63 !important; color: #fff; }
        h2 { font-size: 18px; color: #ffeb3b; padding-left: 10px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>LiTV 頻道清單 (<?php echo count($channels); ?> 台)</h2>
    <?php foreach ($channels as $id => $name): ?>
        <a href="?id=<?php echo $id; ?>" class="channel-item <?php echo ($id == $current_id) ? 'active' : ''; ?>">
            <?php echo htmlspecialchars($name); ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="main">
    <div class="player-container">
        <!-- 直接內嵌 LiTV 官方播放頁面 -->
        <iframe src="https://litv.tv<?php echo $current_id; ?>" allowfullscreen allow="autoplay"></iframe>
    </div>
</div>

</body>
</html>
