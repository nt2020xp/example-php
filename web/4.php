<?php
// filename: ettv.php
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>東森新聞 直播播放</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: black;
        }
        #playerFrame {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>

<iframe id="playerFrame"
        src="https://www.4gtv.tv/channel/4gtv-4gtv152?set=1&ch=292"
        allowfullscreen></iframe>

<script>
    // 嘗試自動進入全螢幕（若支援）
    function goFullScreen() {
        const iframe = document.getElementById('playerFrame');
        const el = iframe;

        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        } else if (el.msRequestFullscreen) {
            el.msRequestFullscreen();
        }
    }

    window.addEventListener('load', function () {
        setTimeout(goFullScreen, 1000);
    });
</script>

</body>
</html>
