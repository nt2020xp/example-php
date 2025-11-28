<?php
error_reporting(0);
$id = isset($_GET['id'])?$_GET['id']:'cctv1';
$n = [
    'cctv1' => [265183188, 265183189], //CCTV1
    'cctv1b' => [265183188, 265183669], //CCTV1
    'cctv2' => [265667329, 265667330], //CCTV2
    'cctv3' => [265667206, 265667207], //CCTV3
    'cctv4' => [265667639, 265667640], //CCTV4
    'cctv4o' => [265667313, 265667314], //CCTV4欧洲
    'cctv4a' => [265667335, 265667336], //CCTV4美洲
    'cctv5' => [265667565, 265667566], //CCTV5
    'cctv5b' => [265667565, 395255638], //CCTV5
    'cctv5p' => [265106763, 265125883], //CCTV5+
    'cctv5p2' => [265106763, 265106764], //CCTV5+
    'cctv6' => [265667482, 265667483], //CCTV6
    'cctv7' => [265667268, 265667269], //CCTV7
    'cctv8' => [265667466, 265667467], //CCTV8
    'cctv9' => [265667202, 265667203], //CCTV9
    'cctv10' => [265667631, 265667632], //CCTV10
    'cctv11' => [265667429, 265667430], //CCTV11
    'cctv12' => [265667607, 265667608], //CCTV12
    'cctv13' => [265667474, 265667476], //CCTV13
    'cctv14' => [265667325, 265667326], //CCTV14
    'cctv15' => [265667535, 265667536], //CCTV15
    'cctv17' => [265667526, 265667527], //CCTV17
    'cgtne' => [265218872, 265218873], //CGTN西语
    'cgtna' => [265219154, 265219155], //CGTN阿语
    'chcjtyy' => [265667645, 265667646], //CHC家庭影院
    'chcdzdy' => [265218967, 265218968], //CHC动作电影
    'chcymdy' => [952383261, 952383262], //CHC影迷电影
    'bjws' => [265668911, 265668912], //北京卫视,
    'dfws' => [264104266, 264104267], //东方卫视
    'dfws2' => [264104266, 266579023], //东方卫视
    'cqws' => [531262033, 531262034], //重庆卫视
    'jlws' => [531262154, 531262155], //吉林卫视
    'lnws' => [265669068, 265669069], //辽宁卫视
    'nmws' => [531261982, 531261983], //内蒙古卫视
    'nxws' => [531261057, 531261058], //宁夏卫视
    'gsws' => [531261933, 531261934], //甘肃卫视
    'qhws' => [531262027, 531262028], //青海卫视
    'sxws' => [816409120, 816409121], //陕西卫视
    'sdws' => [531261825, 531261826], //山东卫视
    'hubws' => [531261978, 531261979], //湖北卫视
    'hunws' => [265667721, 265667722], //湖南卫视
    'jxws' => [810783159, 810784931], //江西卫视
    'jsws' => [264104188, 264104189], //江苏卫视
    'gdws' => [263541274, 275480030], //广东卫视
    'gdws2' => [263541274, 263541275], //广东卫视
    'dwqws' => [265218882, 810455064], //大湾区卫视
    'scws' => [531261937, 531261938], //四川卫视
    'xjws' => [531262095, 531262096], //新疆卫视
    'xzws' => [524854265, 524854266], //西藏卫视
    'hinws' => [531262161, 531262162], //海南卫视
    'dnws' => [810326620, 810454855], //东南卫视
    'hxws' => [810326850, 810455033], //海峡卫视
    
    'shdy' => [265667494, 265667495], //四海钓鱼
    'jsjy' => [265219146, 265219147], //江苏教育
    'sdjy' => [265218942, 265218943], //山东教育卫视
    'yxfy' => [265667664, 265667665], //游戏风云
    'hxjc' => [202812323, 202812324], //欢笑剧场4K
    'dfgw' => [97019370, 97019371], //东方购物
    'zjjl' => [80891335, 80891336], //之江纪录
    'hzzh' => [76680661, 76680662], //杭州综合
    'hzmz' => [76680568, 76680569], //杭州明珠
    'hzsh' => [76680574, 76680575], //杭州生活
    'hzys' => [76680745, 76680746], //杭州影视
    'hzse' => [76680756, 76680757], //杭州少儿体育
    'y' => [140151866, 140151867], //Y+
    'lgs'   => [810326846, 810326847], //老故事
    'fxzl' => [810326624, 810326625], //发现之旅
    'zxs' => [810326679, 810326680], //中学生
    'xpfy' => [265218930, 265218931], //新片放映厅
    'zjsv' => [265218878, 265218879], //追剧少女
    'rbj' => [265218955, 265218956], //热播剧
    'gqdp' => [265218862, 265218863], //高清大片
    'xmhd' => [265667599, 265667600],//熊猫频道高清
    'xm1' => [265219065, 265219066 ],//熊猫频道1
    'xm2' => [265218959, 265218960],//熊猫频道2
    'xm3' => [265218910, 265218911],//熊猫频道3
    'xm4' => [265218991, 265218992],//熊猫频道4
    'xm5' => [265218689, 265218691],//熊猫频道5
    'xm6' => [265218934, 265218935],//熊猫频道6
    'xm7' => [265219037, 265219038],//熊猫频道7
    'xm8' => [265218971, 265218972],//熊猫频道8
    'xm9' => [265218886, 265218887],//熊猫频道9
    'xm10' => [265218794, 265218795],//熊猫频道10
    'mgysdy' => [265219029, 265219030], //咪咕云上电影院
];

if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $onlineip = $_SERVER['REMOTE_ADDR'];
}
$useragent = $_SERVER['HTTP_USER_AGENT'];
$userid = md5($onlineip . $useragent);
$cacheFileName = 'url_cache_all.json';
$urlData = [];
if (file_exists($cacheFileName)) {
    $urlData = json_decode(file_get_contents($cacheFileName), true);
    if (isset($urlData) && isset($urlData['akmg'][$userid]) && isset($urlData['akmg'][$userid]['sessionID'])) {
        $sessionID = $urlData['akmg'][$userid]['sessionID'];
        $pdata = '{"businessType":"BTV","channelID":"'.$n[$id][0].'","mediaID":"'.$n[$id][1].'"}';
        $uri = "http://vschz030163.aikan.miguvideo.com:33200/VSP/V3/PlayChannel";
        $headers = [
            'isEncrypt: 0',
            'EpgSession: JSESSIONID='.$sessionID,
            'Location: http://vschz030163.aikan.miguvideo.com:33200',
            'Cookie: JSESSIONID='.$sessionID.'; arrayid=ZJHZ-YDSP-APP-6PHMServ30; Path=/',
            'User-Agent: Dalvik/2.1.0 (Linux; U; Android 12; V2199GA Build/f70ec1c.0)',
            'Content-Type: application/json; charset=UTF-8',
            'Host: vschz030163.aikan.miguvideo.com:33200',
            'Connection: Keep-Alive',
        ];
        $Playurl = json_decode(get($uri,$headers,$pdata), true)['attachedPlayURL'];
        //print_r(j('1'));
        if ($Playurl) {
            if (isset($_GET['playseek'])) {
                $playseek = $_GET['playseek'];
                list($starttime, $endtime) = explode('-', $playseek);
                $startDateTime = new DateTime($starttime);
                $endDateTime = new DateTime($endtime);
                $startDateTime->modify('-8 hours');
                $endDateTime->modify('-8 hours');
                $starttime = $startDateTime->format('YmdHis');
                $endtime = $endDateTime->format('YmdHis');
                $Playurl = $Playurl . "&playbackbegin=" . $starttime . "&playbackend=" . $endtime;
            }
            header('location:'.$Playurl);
            exit();
        }
    }
}
//print_r(j('1'));
$url = "https://vschz030174.aikan.miguvideo.com:33207/EPG/JSON/ZJMobileAuthenticate";
$header = [
    'Location: http://vschz030174.aikan.miguvideo.com:33200',
    'isEncrypt: 1',
    'Content-Type: application/json; charset=utf-8',
    'Host: vschz030174.aikan.miguvideo.com:33207',
    'Connection: Keep-Alive',
    'User-Agent: okhttp/3.12.13'

];
$post = '{"sign":""}';//完整的登录提交信息，包含{}及里面所有的内容
$d = get($url,$header,$post);
$sessionID = json_decode(j($d), true)['sessionid'];
//print_r($sessionID);
$pdata = '{"businessType":"BTV","channelID":"'.$n[$id][0].'","mediaID":"'.$n[$id][1].'"}';
$uri = "http://vschz030174.aikan.miguvideo.com:33200/VSP/V3/PlayChannel";
$headers = [
    'isEncrypt: 0',
    'EpgSession: JSESSIONID='.$sessionID,
    'Location: http://vschz030174.aikan.miguvideo.com:33200',
    'Cookie: JSESSIONID='.$sessionID.'; arrayid=ZJHZ-YDSP-APP-6PHMServ30; Path=/',
    'User-Agent: Dalvik/2.1.0 (Linux; U; Android 12; V2199GA Build/f70ec1c.0)',
    'Content-Type: application/json; charset=UTF-8',
    'Host: vschz030174.aikan.miguvideo.com:33200',
    'Connection: Keep-Alive',
];
$Playurl = json_decode(get($uri,$headers,$pdata), true)['attachedPlayURL'];
if ($Playurl) {
    $urlData['akmg'][$userid] = [
        'sessionID' => $sessionID
    ];
    file_put_contents($cacheFileName, json_encode($urlData)); 
    if (isset($_GET['playseek'])) {
        $playseek = $_GET['playseek'];
        list($starttime, $endtime) = explode('-', $playseek);
        $startDateTime = new DateTime($starttime);
        $endDateTime = new DateTime($endtime);
        $startDateTime->modify('-8 hours');
        $endDateTime->modify('-8 hours');
        $starttime = $startDateTime->format('YmdHis');
        $endtime = $endDateTime->format('YmdHis');
        $Playurl = $Playurl . "&playbackbegin=" . $starttime . "&playbackend=" . $endtime;
    }
    //print_r($Playurl);    
    header('location:'.$Playurl);
} else {
    echo "获取播放地址失败";
}
function get($url,$h,$post){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$h);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}    
function j($text){
    $data = json_decode($text, true)["signResponse"];
    $ciphertext_base64 = urldecode($data);
    $data = base64_decode($ciphertext_base64);
    $ct_full = substr($data, 0, -64);
    $iv = substr($data, -64, 32);
    $aad = substr($data, -32);
    $ct = substr($ct_full, 0, -16);
    $tag = substr($ct_full, -16);
    $key_hex = "1f2a00ddb0E0EBc5Fab7933cCAaFf62efDcab4eeEf5ad50c64CeFA5AbbbFeee6";
    $key = hex2bin($key_hex);
    $plaintext = openssl_decrypt($ct, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, $aad);
    if ($plaintext === false) {
        return;
    }
    return $plaintext;
}
?>

