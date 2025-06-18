<？php
$head_httplive = 'http://httplive.slave.bfgd.com.cn:14311';
$head_httpstream = 'http://httpstream.slave.bfgd.com.cn:14312';

函數 getcurl（$url）{
$user_agent = “Mozilla/4.0 （相容;微小星 8.0;Windows NT 6.1作系統;三叉戟/4.0）“;
$ch = curl_init（）;
curl_setopt（$ch、CURLOPT_PROXY、$proxy）;
curl_setopt ($ch, CURLOPT_URL, $url); 設置要訪問的IP
curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent); 模擬使用者使用的瀏覽器
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1 ); 使用自動跳轉
curl_setopt ($ch, CURLOPT_TIMEOUT, 60); 設置超時時間
curl_setopt ($ch, CURLOPT_AUTOREFERER, 1 ); 自動設置Referer
curl_setopt ($ch, CURLOPT_HEADER,0); 顯示返回的HEAD區域的內容
curl_setopt （$ch， CURLOPT_RETURNTRANSFER， 1）;
curl_setopt （$ch， CURLOPT_FOLLOWLOCATION， 1）;
curl_setopt（$ch、CURLOPT_TIMEOUT、30）;
$result = curl_exec（$ch）;
curl_close（$ch）;
返回 $result;
}

函數 getinfo_json（$chnlid，$token）{
$i_url = 'http：//slave.bfgd.com.cn/media/channel/get_info？chnlid=4200000'.$chnlid.'&accesstoken='.$token;
$i_result = file_get_contents（$i_url）;
返回 json_decode（$i_result）;
}

$accesstoken='R621C86FCU319FA04BK783FB5EBIFA29A0DEP2BF4M340CAC5V0Z339C9W16D7E5AFCA1ADFD1';

$id=isset（$_GET['id']）？$_GET['id']：'068';
$type=isset（$_GET['type']）？$_GET['type']：'live';

if（$type=='live'）{
    直播
header（“訪問控制允許源：*”）;
$json = getinfo_json（$id，$accesstoken）;
$playtoken = isset（$json->play_token）？$json->play_token：'ABCDEFGHI';
$playurl=$head_httplive。/playurl？playtype=live&protocol=hls&accesstoken='.$accesstoken.'&programid=4200000'.$id.'&playtoken='.$playtoken;
$m 3u8 =getcurl（$playurl）;
回聲 preg_replace（'/（http）：\/\/（[^\/]+）/i'，$head_httplive，$m 3u8）;
}else if（$type=='list'）{
    節目單
$date=isset（$_GET['date']）？$_GET['date']:d ate（'Y-m-d'）;
$time = 時間 （）;
$json = getinfo_json（$id，$accesstoken）;
    echo $json->chnl_name." “.$date.” 節目單<br/>“;
$list_url='http：//slave.bfgd.com.cn/media/event/get_list？chnlid=4200000'.$id.'&pageidx=1&vcontrol=0&attachdesc=1&repeat=1&accesstoken='.$accesstoken.'&starttime='.strtotime（$date）.'&endtime='.strtotime（'+1 天'，strtotime（$date））.'&pagenum=100&flagposter=0';
$list_result = file_get_contents（$list_url）;
$list_json = json_decode（$list_result）;
$event_list=$list_json->event_list;
for （$x=0; $x<count（$event_list）; $x++） {
$url='bfgd.php？type=back&start='.date（'YmdHis'，$event_list[$x]->start_time）.'&end='.date（'YmdHis'，$event_list[$x]->end_time）.'&event_id='.$event_list[$x]->event_id;
$n=date（'H：i'，$event_list[$x]->start_time）.''.$event_list[$x]->event_name;
if（number_format（$time）>number_format（$event_list[$x]->end_time））{
echo “<a href='{$url}' title=''>$n</a><br/>”;
}else{
回聲 $n.“<br/>;”
        }
    }
}else if（$type=='back'）{
    回看
header（“訪問控制允許源：*”）;
$start=$_GET['start'];
$end=$_GET['end'];
$eventid=$_GET['event_id'];
$url='http：//slave.bfgd.com.cn/media/event/get_info？accesstoken='.$accesstoken.'&eventid='.$eventid;
$result = file_get_contents（$url）;
$json = json_decode（$result）;
_playtoken 美元 = $json->play_token;
$playurl=$head_httpstream。/playurl？playtype=lookback&protocol=hls&starttime='.$start.'&endtime='.$end.'&accesstoken='.$accesstoken.'&programid='.$eventid.'&playtoken='.$_playtoken;
$m 3u8 =getcurl（$playurl）;
回聲 preg_replace（'/（http）：\/\/（[^\/]+）/i'，$head_httpstream，$m 3u8）;
}
?>
