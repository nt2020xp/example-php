<?php
error_reporting(0);
$n = [
    'cctv1' => '608807420', //CCTV1综合
    'cctv2' => '631780532', //CCTV2财*
    'cctv3' => '624878271', //CCTV3综艺
    'cctv4' => '631780421', //CCTV4中文国际
    'cctv4a' => '608807416', //CCTV4美洲
    'cctv4o' => '608807419', //CCTV4欧洲
    'cctv5' => '641886683', //CCTV5体育x
    'cctv5p' => '641886773', //CCTV5+体育赛事x
    'cctv6' => '624878396', //CCTV6电影
    'cctv7' => '673168121', //CCTV7国防军事
    'cctv8' => '624878356', //CCTV8电视剧
    'cctv9' => '673168140', //CCTV9纪录
    'cctv10' => '624878405', //CCTV10科教
    'cctv11' => '667987558', //CCTV11戏曲
    'cctv12' => '673168185', //CCTV12社会与法
    'cctv13' => '608807423', //CCTV13新闻
    'cctv14' => '624878440', //CCTV14少儿
    'cctv15' => '673168223', //CCTV15音乐
    'cctv17' => '673168256', //CCTV17农业农村

    'fxzl' => '624878970', //CCTV发现之旅
    'lgs' => '884121956', //CCTV老故事
    'zxs' => '708869532', //CCTV中学生

    'cgtn' => '609017205', //CGTN
    'cgtnjl' => '609006487', //CGTN纪录
    'cgtne' => '609006450', //CCTV西班牙语
    'cgtnf' => '609006476', //CCTV法语
    'cgtna' => '609154345', //CCTV阿拉伯语
    'cgtnr' => '609006446', //CCTV俄语

    'cetv1' => '923287154', //CETV1中教1台
    'cetv2' => '923287211', //CETV2中教2台
    'cetv4' => '923287339', //CETV4中教3台

    'dfws' => '651632648', //东方卫视
    'jlws' => '947472500', //吉林卫视
    'qhws' => '947472506', //青海卫视
    'sxws' => '738910838', //陕西卫视
    'hnws' => '790187291', //河南卫视
    'hubws' => '947472496', //湖北卫视
    'jxws' => '783847495', //江西卫视
    'jsws' => '623899368', //江苏卫视
    'dnws' => '849116810', //东南卫视
    'hxws' => '849119120', //海峡卫视
    'gdws' => '608831231', //广东卫视
    'dwqws' => '608917627', //大湾区卫视
    'hinws' => '947472502', //海南卫视

    'dfys' => '617290047', //东方影视
    'shxwzh' => '651632657', //上海新闻综合
    'dycj' => '608780988', //上海第一财*
    'fztd' => '790188943', //法治天地
    'jbty' => '796071336', //劲爆体育
    'mlzq' => '796070308', //魅力足球
    'ly' => '796070452', //乐游
    'yxfy' => '790188417', //游戏风云

    'jscs' => '626064714', //江苏城市
    'jszy' => '626065193', //江苏综艺
    'jsys' => '626064697', //江苏影视
    'jsggxw' => '626064693', //江苏公共新闻
    'jsgj' => '626064674', //江苏国际
    'jsjy' => '628008321', //江苏教育
    'jstyxx' => '626064707', //江苏体育休闲
    'ymkt' => '626064703', //优漫卡通

    'njxwzh' => '838109047', //南京新闻综合
    'njkj' => '838153729', //南京教科
    'njsb' => '838151753', //南京十八

    'haxwzh' => '639731826', //淮安新闻综合
    'lygxwzh' => '639731715', //连云港新闻综合
    'szxwzh' => '639731952', //苏州新闻综合
    'tzxwzh' => '639731818', //泰州新闻综合
    'sqxwzh' => '639731832', //宿迁新闻综合
    'xzxwzh' => '639731747', //徐州新闻综合

    'gdys' => '614961829', //广东影视
    'jjkt' => '614952364', //嘉佳卡通

    'gdjys' => '631354620', //掼蛋精英赛
    'gqdp' => '629943678', //高清大片
    'hslbt' => '713600957', //红色轮播台
    'jddhdjh' => '629942219', //经典动画大集合
    'jdxgdy' => '625703337', //经典香港电影
    'jmbkdp' => '617432318', //军事迷必看大片
    'jsjc' => '713591450', //金色剧场
    'mg24hty' => '654102378', //咪咕24小时体育台
    'nbajd' => '788815380', //NBA经典
    'sszjd' => '646596895', //赛事最经典
    'ttmlh' => '629943305', //体坛名栏汇
    'wdlsjd' => '780288994', //五大联赛经典
    'xdllxjh' => '713589837', //新动力量宣讲会
    'xpfyt' => '619495952', //新片放映厅
    'zgzqfy' => '788816794', //中国足球风云
    'zqzyp' => '629942228', //最强综艺趴

    'xmpd' => '609158151', //熊猫01高清
    'xm1' => '608933610', //熊猫1
    'xm2' => '608933640', //熊猫2
    'xm3' => '608934619', //熊猫3
    'xm4' => '608934721', //熊猫4
    'xm5' => '608935104', //熊猫5
    'xm6' => '608935797', //熊猫6
    'xm7' => '609169286', //熊猫7
    'xm8' => '609169287', //熊猫8
    'xm9' => '609169226', //熊猫9
    'xm10' => '609169285', //熊猫10
];

$id = isset($_GET['id']) ? $_GET['id'] : 'shxwzh';
$url = "https://webapi.miguvideo.com/gateway/playurl/v2/play/playurlh5?contId={$n[$id]}&rateType=3&channelId=0131_10010001005";
$json = json_decode(get_data($url));
$live = $json->body->urlInfo->url;

$uas = parse_url($live);
parse_str($uas["query"], $arr);
$puData = str_split($arr['puData']);
$ProgramID = str_split($n[$id]);
$Program = str_split('yzwxcdwbgh');

$s = count($puData);
for ($v = 0; $v < $s / 2; $v++) {
    $arr_key[] = $puData[$s - $v - 1];
    $arr_key[] = $puData[$v];
    switch ($v) {
        case 1:
            $arr_key[] = arrkey($v);
            break;
        case 2:
            $arr_key[] = arrkey($v);
            break;
        case 3:
            $chars = $Program[$ProgramID[1]];
            $arr_key[] = $chars;
            break;
        case 4:
            $arr_key[] = arrkey($v);
            break;
        }
    }
$ddCalcu = join($arr_key);

$p = $live . "&ddCalcu=" . $ddCalcu . '&sv=10000&crossdomain=www&ct=www';
$playurl = get_data($p);
header('Location:' . $playurl);
print_r($playurl);

function arrkey($v){
    $put = ['z', 'y', '0', 'z'];
    $mark = $put[$v - 1];
    return $mark;
    }

function get_data($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
?>
