<?php
// =================== 配置 ===================
$baseUrl = "http://192.168.2.3:8091/migu4k.php?id="; //替换为你自己的php代理

// =================== 工具函数 ===================
function getJson($url) {
    $json = @file_get_contents($url);
    return $json ? json_decode($json, true) : null;
}

// =================== 生成赛事 m3u ===================
function buildMatchM3U($baseUrl, $withHeader = true) {
    $url = "https://vms-sc.miguvideo.com/vms-match/v6/staticcache/basic/match-list/normal-match-list/0/all/default/1/miguvideo";
    $data = getJson($url);
    
    if (!$data || empty($data['body']['matchList'])) {
        return $withHeader ? "#EXTM3U\n" : "";
    }
    
    // =================== 按日期分组 ===================
    $m3u = $withHeader ? "#EXTM3U\n" : "";
    foreach ($data['body']['matchList'] as $day => $matches) {
        foreach ($matches as $match) {
            $competitionDate = $match['keyword'] ?? "";
            $title = $competitionDate.'*'.$match['competitionName'].'*'.$match['pkInfoTitle'];
            $logo = $match['competitionLogo'] ?? "";
            $pID = $match['pID'] ?? '0';

            $m3u .= "#EXTINF:-1 tvg-logo=\"{$logo}\" group-title=\"{$day}\",{$title}\n";
            $m3u .= $baseUrl . $pID . "\n";
        }
    }
    
    // =================== 热门比赛 ===================
    if (!empty($data['body']['hotMatchList'])) {
        foreach ($data['body']['hotMatchList'] as $match) {
            $title = $match['keyword'].'*'.$match['modifyTitle'].'*'.$match['title'];
            $logo  = $match['competitionLogo'] ?? "";
            $pID   = $match['pID'] ?? '0';
    
            $m3u .= "#EXTINF:-1 tvg-logo=\"{$logo}\" group-title=\"热门比赛\",{$title}\n";
            $m3u .= $baseUrl . $pID . "\n";
        }
    }
    
    return $m3u;
}

// =================== 生成直播频道 m3u ===================
function buildLiveM3U($baseUrl, $withHeader = true) {
    // 入口接口：热门组
    $mainUrl = "https://live.miguvideo.com/live/v2/tv-data/e7716fea6aa1483c80cfc10b7795fcb8";
    $mainData = getJson($mainUrl);
    if (!$mainData || empty($mainData['body']['liveList'])) {
        return $withHeader ? "#EXTM3U\n" : "";
    }

    $m3u = $withHeader ? "#EXTM3U\n" : "";

    foreach ($mainData['body']['liveList'] as $group) {
        $groupName = $group['name'];
        $vomsID = $group['vomsID'];

        $url = "https://live.miguvideo.com/live/v2/tv-data/" . $vomsID;
        $data = getJson($url);
        if (!$data || empty($data['body']['dataList'])) {
            continue;
        }

        foreach ($data['body']['dataList'] as $ch) {
            $name = $ch['name'];
            $pID  = $ch['pID'];
            $logo = $ch['h5pics']['highResolutionH'] ?? "";

            $m3u .= "#EXTINF:-1 tvg-id=\"{$name}\" tvg-name=\"{$name}\" tvg-logo=\"{$logo}\" group-title=\"{$groupName}\",{$name}\n";
            $m3u .= $baseUrl . $pID . "\n";
        }
    }

    return $m3u;
}

// =================== 路由选择 ===================
$type = $_GET['type'] ?? 'live'; // 默认 live

if ($type === 'match') {
    $m3u = buildMatchM3U($baseUrl);
    $filename = "match.m3u";
} elseif ($type === 'all') {
    $m3u  = "#EXTM3U\n"; // 总文件只写一次头
    $m3u .= buildMatchM3U($baseUrl, false);
    $m3u .= buildLiveM3U($baseUrl, false);
    $filename = "all.m3u";
} else {
    $m3u = buildLiveM3U($baseUrl);
    $filename = "live.m3u";
}

header("Content-Type: audio/x-mpegurl");
header("Content-Disposition: inline; filename={$filename}");
echo $m3u;
