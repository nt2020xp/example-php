<?php
//Written by Wheiss
//http://127.0.0.1/yditvfifa.php?channel-id=FifastbLive&Contentid=3000000020000011528&livemode=1&stbId=toShengfen&/PLTV/&hostip=&$8M FHD
error_reporting(0);
date_default_timezone_set("PRC");
$hostip = $_GET['hostip']??'';
if (!$hostip) {//当没有设置hostip时，随机取
        $domain = "mgsp-ws2.live.miguvideo.com.wsdvs.com";
        $ipsAArray = dns_get_record($domain, DNS_A);
        $hostip = $ipsAArray[rand(0, count($ipsAArray) - 1)]['ip'];
}
$channel_id = $_GET['channel-id']??'FifastbLive';
$Contentid = $_GET['Contentid']??'3000000020000011528';
$stbId = $_GET['stbId']??'toShengfen';
$playseek = $_GET['playseek']??'';
switch ($channel_id) {
        case 'bestzb':
                $domain_id = 'bestlive';
                break;
        case 'wasusyt':
                $domain_id = 'wasulive';
                break;
        case 'FifastbLive':
                $domain_id = 'fifalive';
                break;
        default:
                $domain_id = $channel_id;
                break;
}
if ($playseek) {
        $playseekArray = str_split(str_replace('-','.0',$playseek).'.0',8);
        $starttime = $playseekArray[0].'T'.$playseekArray[1].'0Z';
        $endtime = $playseekArray[2].'T'.$playseekArray[3].'0Z';
        $url1 = "http://gslbserv.itv.cmvideo.cn/index.m3u8?channel-id={$channel_id}&Contentid={$Contentid}&livemode=4&stbId={$stbId}&starttime={$starttime}&endtime={$endtime}";
} else {
        $url1 = "http://gslbserv.itv.cmvideo.cn/index.m3u8?channel-id={$channel_id}&Contentid={$Contentid}&livemode=1&stbId={$stbId}";
}
$url2 = get_redirect_url($url1,["User-Agent: okhttp/3.12.3"]);
if (!$url2) $url2 = get_redirect_url(str_replace('gslbserv.itv.cmvideo.cn','36.155.98.21',$url1),["User-Agent: okhttp/3.12.3","Host: gslbserv.itv.cmvideo.cn"]);
$position = strpos($url2,'/',8);
$str = substr($url2,$position);
$url3 = "http://{$hostip}/cache.ott.{$domain_id}.itv.cmvideo.cn{$str}";
header("location:$url3");
exit;

function get_redirect_url($url,$headers=["User-Agent: okhttp/3.12.3"]) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        return $redirectUrl;
}
?>
