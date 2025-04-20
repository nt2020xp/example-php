<?php
    $channels = array(
            'cctv4k'=>'600002264',
            'cctv1'=>'600001859',
            'cctv2'=>'600001800',
            'cctv3'=>'600001801',
            'cctv4'=>'600001814',
            'cctv5'=>'600001818',
            'cctv5p'=>'600001817',
            'cctv6'=>'600001802',
            'cctv7'=>'600004092',
            'cctv8'=>'600001803',
            'cctv9'=>'600004078',
            'cctv10'=>'600001805',
            'cctv11'=>'600001806',
            'cctv12'=>'600001807',
            'cctv13'=>'600001811',
            'cctv14'=>'600001809',
            'cctv15'=>'600001815',
            'cctv17'=>'600001810',
            'zjws'=>'600002520',
            'jsws'=>'600002521',
            'szws'=>'600002481',
            'gdws'=>'600002485',
            'hljws'=>'600002498',
            'dfws'=>'600002483',
            'flws'=>'600002475'
    );
    $pid = $channels[$_GET['pid']];
    if(!$pid){
            die('need pid');
    }
    $lua_cmd =urlencode("
    function main(splash)
            splash:go('[url=https://m.yangshipin.cn/video?type=1&pid=$pid')]https://m.yangshipin.cn/video?type=1&pid=$pid')[/url]
        splash:wait(0.5)
        splash:mouse_click(305, 305)
        splash:wait(0.1)
        return splash:har()
    end");
    $source = curl_get_contents("http://splash_api_address/execute?lua_source=$lua_cmd");
    preg_match('/https:\/\/liveinfo(.*?)"/', $source, $output);
    $api_url = str_replace('&defn=&', '&defn=fhd&', '[url=https://liveinfo'.$output[1]);]https://liveinfo'.$output[1]);[/url]
    $result = curl_get_contents($api_url);
    preg_match('/"playurl":"(.*?)\?from=player/', $result, $output);
    $play_url = $output[1];
    //header("Content-Type: audio/mpegurl");
    //header("Content-Disposition: attachment; filename=playlist.m3u");
    echo "#EXTM3U\r\n#EXTINF:-1 tvg-name='".$_GET['pid']."', ".$_GET['pid']."\r\n".$play_url;

    function curl_get_contents($url)
    {
            $header = array(
            'authority: liveinfo.yangshipin.cn',
            'user-agent: Mozilla/5.0 (Windows NT 10.2; Win64; x64) AppleWebKit/888.36 (KHTML, like Gecko) Chrome/88',
            'accept: */*',
            'referer: [url=https://m.yangshipin.cn/video?']https://m.yangshipin.cn/video?'[/url]
            );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
?>
