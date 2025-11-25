<？php
error_reporting（0）;

// 频道映射表
$n = [
“CCTV1” => [“608807420”，“n”]， //CCTV1综合
“CCTV2” => [“631780532”，“n”]， //CCTV2财经
“cctv3” => [“624878271”，“m”]， //CCTV3综艺
“CCTV4” => [“631780421”，“n”]， //CCTV4中文国际
“CCTV4a” => [“608807416”，“n”]， //CCTV4美洲
“CCTV4o” => [“608807419”，“n”]， //CCTV4欧洲
“CCTV6” => [“624878396”，“m”]， //CCTV6电影
“CCTV7” => [“673168121”，“m”]， //CCTV7国防军事
“CCTV8” => [“624878356”，“m”]， //CCTV8电视剧
“CCTV9” => [“673168140”，“m”]， //CCTV9纪录
“CCTV10” => [“624878405”，“m”]， //CCTV10科教
“CCTV11” => [“667987558”，“n”]， //CCTV11戏曲
“CCTV12” => [“673168185”，“m”]， //CCTV12社会与法
“CCTV13” => [“608807423”，“n”]， //CCTV13新闻
“CCTV14” => [“624878440”，“m”]， //CCTV14少儿
“cctv15” => [“673168223”，“m”]， //CCTV15音乐
“CCTV17” => [“673168256”，“m”]， //CCTV17农业农村
“fxzl” => [“624878970”，“m”]， //发现之旅
“lgs” => [“884121956”，“n”]， //老故事
“zxs” => [“708869532”，“m”]， //中学生
“cgtn” => [“609017205”，“n”]， //CGTN
“cgtna” => [“609154345”，“n”]， //CGTN阿拉伯语
“cgtne” => [“609006450”，“n”]， //CGTN西班牙语
“cgtnf” => [“609006476”，“n”]， //CGTN法语
“cgtnjl” => [“609006487”，“n”]， //CGTN外语纪录
“cgtnr” => [“609006446”，“n”]， //CGTN俄语
“cetv1” => [“923287154”，“n”]， //CETV1
“cetv2” => [“923287211”，“n”]， //CETV2
“cetv4” => [“923287339”，“n”]， //CETV4
“chcym” => [“952383261”，“n”]， //CHC影迷电影
“dfws” => [“651632648”，“n”]， //东方卫视
“jlws” => [“947472500”，“n”]， //吉林卫视
“qhws” => [“947472506”，“n”]， //青海卫视
“sxws” => [“738910838”，“n”]， //陕西卫视
“nlws” => [“956904896”，“n”]， //农林卫视
“hnws” => [“790187291”，“n”]， //河南卫视
“hubws” => [“947472496”，“n”]， //湖北卫视
“jxws” => [“783847495”，“n”]， //江西卫视
“jsws” => [“623899368”，“m”]， //江苏卫视
“dnws” => [“849116810”，“n”]， //东南卫视
“hxws” => [“849119120”，“m”]， //海峡卫视
“gdws” => [“608831231”，“n”]， //广东卫视
“dwqws” => [“608917627”，“n”]， //大湾区卫视
“btws” => [“956923145”，“n”]， //兵团卫视
“hinws” => [“947472502”，“n”]， //海南卫视
“shdy” => [“637444975”，“n”]， //四海钓鱼
“dfys” => [“617290047”，“n”]， //上视东方影视
“shxwzh” => [“651632657”，“n”]， //上海新闻综合
“dycj” => [“608780988”，“n”]， //上海第一财经
“sxxwzx” => [“956909289”，“m”]， //陕西新闻资讯
“sxdsqc” => [“956909358”，“m”]， //陕西都市青春
“sxtyxx” => [“956909356”，“m”]， //陕西体育休闲
“sxyl” => [“956909362”，“m”]， //陕西银龄
“sxqq” => [“956909303”，“m”]， //陕西秦腔
“jscs” => [“626064714”，“n”]， //江苏城市
“jsys” => [“626064697”，“n”]， //江苏影视
“jszy” => [“626065193”，“n”]， //江苏综艺
“jsggxw” => [“626064693”，“n”]， //江苏公共新闻
“jstyxx” => [“626064707”，“n”]， //江苏体育休闲
“jsjy” => [“628008321”，“m”]， //江苏教育
“jsgj” => [“626064674”，“n”]， //江苏国际
“ymkt” => [“626064703”，“n”]， //优漫卡通
“cftx” => [“956923159”，“n”]， //财富天下
“njxwzh” => [“838109047”，“m”]， //南京新闻综合
“njkj” => [“838153729”，“n”]， //南京教科
“njsb” => [“838151753”，“n”]， //南京十八
“haxwzh” => [“639731826”，“n”]， //淮安新闻综合
“lygxwzh” => [“639731715”，“n”]， //连云港新闻综合
“ntxwzh” => [“955227985”，“n”]， //南通新闻综合
“sqxwzh” => [“639731832”，“n”]， //宿迁新闻综合
“tzxwzh” => [“639731818”，“n”]， //泰州新闻综合
“xzxwzh” => [“639731747”，“n”]， //徐州新闻综合
“ycxwzh” => [“639731825”，“n”]， //盐城新闻综合
“yxxwzh” => [“955227996”，“n”]， //宜兴新闻综合
“jyxwzh” => [“955227979”，“n”]， //江阴新闻综合
“lsxwzh” => [“639737327”，“n”]， //溧水新闻综合
“gdys” => [“614961829”，“n”]， //广东影视
“jjkt” => [“614952364”，“n”]， //嘉佳卡通
“whcq” => [“707671890”，“n”]， //五环传奇
“gdjys” => [“631354620”，“n”]， //掼蛋精英赛
“mg24hty” => [“654102378”，“n”]， //咪咕24小时体育台
“jdxgdy” => [“625703337”，“n”]， //经典香港电影
“kzjdyp” => [“617432318”，“n”]， //抗战经典影片
“xpfyt” => [“619495952”，“n”]， //新片放映厅
“xssh” => [“713600957”，“n”]， //血色山河·抗日战争影像志
“xdll” => [“713589837”，“m”]， //新动力量创一流
“qtj” => [“647370520”，“n”]， //钱塘江
“xmhd” => [“609158151”，“m”]， //熊猫频道01高清
“xm1” => [“608933610”，“n”]， //熊猫频道1
“xm2” => [“608933640”，“n”]， //熊猫频道2
“xm3” => [“608934619”，“n”]， //熊猫频道3
“xm4” => [“608934721”，“n”]， //熊猫频道4
“xm5” => [“608935104”，“n”]， //熊猫频道5
“xm6” => [“608935797”，“n”]， //熊猫频道6
“xm7” => [“609169286”，“m”]， //熊猫频道7
“xm8” => [“609169287”，“m”]， //熊猫频道8
“xm9” => [“609169226”，“m”]， //熊猫频道9
“xm10” => [“609169285”，“m”]， //熊猫频道10
];

// 配置信息 - 请修改为自己的信息
$userId = “写自己的用户ID”;
$userToken = “写自己的usertoken”;

$id = isset（$_GET['id']） ？$_GET['id'] ： 'CCTV1';

// 获取播放地址
$contId = $n[$id][0];
$playurl = handle_migu_main_request（$contId）;

如果 （$playurl） {
header（“位置： ” .$playurl）;
退出;
}

// ==================== 主要处理函数 ====================
函数 handle_migu_main_request（$contId） {
[$cached， $hit] = get_migu_cache（$contId）;
if （$hit） 返回$cached;

全球$userId、$userToken;

[$tm， $saltSign] = get_sign_config（$contId）;
$salt = $saltSign[0];
$sign = $saltSign[1];

$url = 冲刺 f（
“https://play.miguvideo.com/playurl/v1/play/playurl?audio=false&contId=%s&dolby=true&multiViewN=2&h265=true&&os=13&ott=true&rateType=8&salt=%s&sign=%s&timestamp=%s&ua=oneplus-13&vr=true”，
$contId、$salt、$sign$tm
    );

$headers = [
“主机”=>“play.miguvideo.com”，
“appId” => “miguvideo”，
“terminalId” => “安卓”，
“User-Agent” => “Dalvik/2.1.0+（Linux;+U;+Android+13;+oneplus-13+Build/TP1A.220624.014）”，
“MG-BH” => “真”，
“userToken” => $userToken，
“appVersion” => “2600037000”，
“电话信息” => “oneplus-13|13”，
“X-UP-CLIENT-CHANNEL-ID” => “2600037000-99000-200300220100002”，
“用户 ID” => $userId，
“应用版本代码” => “260370016”，
“接受” => “*/*”，
“连接”=>“保持活动”，
    ];

$body = send_get_request（$url， $headers）;
if （$body === null） 返回 null;

$json = json_decode（$body， true）;
if （！is_array（$json）） 返回 null;

$rawUrl = “”;
如果 （isset（$json[“body”][“urlInfo”][“url”]）） {
$rawUrl = （字符串）$json[“正文”][“urlInfo”][“url”];
    }

$ottUrl = migu_encrypted_url（$rawUrl）;
if （trim（$ottUrl） === “”） 返回 null;

set_migu_cache（$contId，$ottUrl，1800 年）;
返回$ottUrl;
}

// ==================== 工具函数 ====================
函数 send_get_request（$url， $headers） {
$ch = curl_init（$url）;
$h = [];
foreach （$headers as $k => $v） $h[] = $k 。": " .$v;
curl_setopt_array（$ch， [
CURLOPT_RETURNTRANSFER => true，
CURLOPT_HEADER => false，
CURLOPT_HTTPHEADER => $h，
CURLOPT_SSL_VERIFYPEER => false，
CURLOPT_SSL_VERIFYHOST => false，
CURLOPT_TIMEOUT => 10，
    ]);
$body = curl_exec（$ch）;
$err = curl_errno（$ch）;
curl_close（$ch）;
if （$err） 返回 null;
返回$body;
}

函数 salt_table（） {
返回 [
“7c8ddcab45b340ecbb02bc979c7f58c8”， “7a5d79ed05ed48c4908b0179d5a5eb2c”， “2195d5312d114db397bcfb5ade3784cf”，
“5454203c18274e8a961efe328a59d1f9”， “aa4688fcf6844e809cb428dc4bd5f265”， “96f3d14a9fe144589f10c775e0c9b4b0”，
“fbc05527db71425f8094e659d62eb878”， “c8e65a8b8b3d46f89397573bfa06f68a”， “a1ef732fa53846c3ba96ada1dcf2513d”，
“48172134576c43889a5b82f0e2809779”， “b7334bae489846ccb5b04574e62c9b7c”， “5e6c8bee9ad449488d6a60a82ae6e2dc”，
“78c979d58cdb4caa849229a11363bd7c”， “5889ccde4570438790be07ee2d8ecde0”， “f3ebbe5ad4cf42569d1450780789fa2d”，
“60e57db48d1c4a0f9d614d1ce43fa865”， “ee892b1bd1074dd7bbd6ab84c3b21fc4”， “df0d80d82df84a9590469ad943a758c2”，
“7ce5870b296d42119dea1b0780892167”， “0637030a22db41c78615d67cfc42da04”， “803b409a8df045f990b4cabde9e3cce5”，
“869973ee8f3543599600cd838503475a”， “6c83090c16f84d57a987edd3bdd11599”， “926ca9cb02674db69e50afde57d8c67b”，
“3ce941cc3cbc40528bfd1c64f9fdf6c0”， “eb3de9fccd40429ab7480d857308612d”， “980ff7db262f49e1820075a7d932deb5”，
“906c7c50da224618”， “f81d1140ebb94bbba74baf5858cf132e”， “dbfd1cfe66ee4bbc8cdb13ba8758b8fc”，
“fe57125553fe4cbdbe12abf7c7cd6ed1”， “5be7f5b3331f4a6e95f6976d7aaeaa28”， “68979e717f0b424ba64c8e53ecbcc8ac”，
“4e61f98facc64fb2a4b91eaea736a5c7”， “f05aa2f4f2124faa89802fd01d3ba436”， “32f3994485ff48b3bce430ba3618ba39”，
“be0ff8b380444ef69f775729c0c191b0”， “b30ccc7e48ec469c945aa546757e9ab4”， “bc1deb4002b44ddf8d181f1972a3cb6a”，
“4bcc96256014c4172878”， “37856b8633a841aebfb76d0ff596b9df”， “ab2fcbf4d28c4ac9897f867af6c25f9d”，
“535200fa8b5d41db8f95ad6bd9033b48”， “75a9af00d3da4ceebc2a70018aea842c”， “08398753dbdb4bcab39a5ce820d220bd”，
“2d0dc516945f4c03b462571bca898234”， “c3f3f929917547af8b8dd76b7eafbfa8”， “2c5190bb4e244501a4f1be0e8de5015e”，
“035f814657c44324bc4fe073898f0789”， “df6d1836cf7b4a118cbca68a5103dc7b”， “a27aceb56688403fa5163c0ae02987dc”，
“a8ef203282724897a707d8c3f264f7ad”， “16264f8569764a5b932c5a6e4206f487”， “fea3a87c09bb406182796c08943713ae”，
“85b14746e58c4b33a56abec13cd3290b”， “61e74ad5375f479d86c4997336bbc459”， “b3991eed9c734f06a721ba97e43024a1”，
“11fba116b6474a16955b738490f8983f”， “f8a5e365c8a448888dce771e27d0a6c7”， “773a341ae0e542088c9655dbd232d1bd”，
“863f85c3032e4012b3c87d5b52d5fb8c”， “3e6098467d6c40828e6412293148648f”， “c5d2cc3c27aa44ec9c2daca6045d7e8e”，
“4ead9ffe0aa04f8c93a7”， “29748e6741b142ffa67a9f9c7411eece”， “932dcb4ba6084e1eb4d2639cf7c64d9c”，
“c0e6340469604cf7ae3cc7c9e5db0f66”， “c387dbdaf30d496aa1b7a3dda102bf08”， “3281503f3dfe4ed3b474cd550d229cd7”，
“10187fd2e6504a8bbd4e1c1b88d9a0f1”， “608489b77e6f4d1c8e90f5c60003a698”， “2992342e0cb74c7491a7480d97041dcc”，
“e14825a4dcaa45ffb222a519f292a61f”， “bc648171ed0b46ff93396746e3d96a88”， “44a41389e4df4f7da6c7c48eff751266”，
“14db12bdfdc94c92941b7df55e8e5d15”， “a23350dfdb2247aa832c3251ccc560b2”， “c1a62f301b6e4f119f4c6a278d660e68”，
“f458f40c3d1c44ef833a8a5e933df839”， “a1bcb6ca2ebd4f5497d0e83c3534c319”， “068d8051a7324a5bbf9fbe0e1c199062”，
“da2e57efa74a4eca847a346513f66c9d”， “e4876dd41a5d4136886ded262ae7d522”， “60e88c6eb37e4edbb1014e3785e64a1b”，
“fe996131d5e0429688cb7e8c990cf6a9”， “e8ba509d0d094c068f9d00d627c59c6e”， “66f1f4a79c8c4cdfaf8f27a12b1625fc”，
“865002edf65543abb23b0177da39d602”， “d1196d8595cb459f82e7ae5bc460441d”， “55ce840e78e04c28951fa5ebac61e66f”，
“4ad156b75be24f8a9732d516079ce872”， “e1d757845a4c4a6690f640ca817675ba”， “dd8982151e9b4be4b1324cb59b24f5de”，
“1d7ff52f2bf046b09d08731587459c0d”， “4bb5342486844c1689d9ba7a676bab87”， “5d6936ea19004c698739e2cfe82fc968”，
“a8e968e5cf934cd29197c2bfa5186cc1”， “11422727790b4f29a356ac2730aa2d0b”， “1842dbb5cec54267b9ed05ec64fd59b6”，
“58cf465392214c91bc18bd8c46d3b109”
    ];
}

函数 migu_cache_dir（）： string {
$dir = __DIR__ 。'/migucache';
如果 （！is_dir（$dir）） {
@mkdir（$dir， 0775， true）;
    }
返回$dir;
}

函数 cache_path（$key） {
返回 migu_cache_dir（） 。“/migu_cache_” 。MD5（$key） 。“.json”;
}

函数 get_migu_cache（$key） {
$p = cache_path（$key）;
如果 （！is_file（$p）） 返回 [null， false];
$d = json_decode（@file_get_contents（$p）， true）;
如果 （！$d） 返回 [null， false];
if （time（） - intval（$d['time']） > intval（$d['ttl']）） {
@unlink（$p）;
返回 [null， false];
    }
返回 [$d['url']， true];
}

函数 set_migu_cache（$key， $url， $ttl_seconds） {
$p = cache_path（$key）;
@file_put_contents（$p， json_encode（['url' => $url， 'time' => time（）， 'ttl' => $ttl_seconds]， JSON_UNESCAPED_SLASHES））;
}

函数 url_sign（$md 5string） {
$salt = strval（random_int（10000000， 99999999））;
$saltInt = intval（substr（$salt， 6））;
$idx = $saltInt % 100;
$table = salt_table（）;
$text = $md 5字符串 .$table[$idx] 。“咪咕”。substr（$salt， 0， 4）;
$sign = md5（$text）;
返回 [$salt， $sign];
}

函数 get_sign_config（$contId） {
$appVersion = “2600037000”;
$tm = （字符串）intval（microtime（true） * 1000）;
$md 5string = md5（$tm . $contId . substr（$appVersion， 0， 8））;
返回 [$tm， url_sign（$md 5string）];
}

函数 migu_encrypted_url（$str） {
if （trim（$str） === “”） {
返回“”;
    }

$parts = parse_url（$str）;
if （$parts === false || ！isset（$parts['query']）） {
返回“”;
    }

parse_str（$parts['查询']， $q）;

$S = isset（$q['puData']） ？$q['puData'] ： “”;
$U = isset（$q['userid']） ？$q['用户 ID'] ： “”;
$T = isset（$q['时间戳']） ？$q['时间戳'] ： “”;
$P = isset（$q['ProgramID']） ？$q['ProgramID'] ： “”;
$C = isset（$q['Channel_ID']） ？$q['Channel_ID'] ： “”;
$V = isset（$q['playurlVersion']） ？$q['playurlVersion'] ： “”;

$sRunes = preg_split（'//u'， $S， -1， PREG_SPLIT_NO_EMPTY）;
$N = 计数（$sRunes）;
$half = （整数）（（$N + 1） / 2）;

$sb = “”;

对于 （$i = 0; $i < $half; $i++） {
if （$N % 2 == 1 & $i == $half - 1） {
$sb .= $sRunes[$i];
破;
        }

$sb .= $sRunes[$N - 1 - $i];
$sb .= $sRunes[$i];

开关 （$i） {
案例一：
$uRunes = preg_split（'//u'， $U， -1， PREG_SPLIT_NO_EMPTY）;
如果 （count（$uRunes） > 2） {
$sb .= $uRunes[2];
} 否则 {
$vRunes = preg_split（'//u'， $V， -1， PREG_SPLIT_NO_EMPTY）;
如果 （计数（$vRunes） > 0） {
$sb .= mb_strtolower（$vRunes[count（$vRunes） - 1]， 'UTF-8'）;
                    }
                }
破;
案例二：
$tRunes = preg_split（'//u'， $T， -1， PREG_SPLIT_NO_EMPTY）;
if （count（$tRunes） > 6） {
$sb .= $tRunes[6];
} 否则 {
$sb .= $sRunes[$i];
                }
破;
案例三：
$pRunes = preg_split（'//u'， $P， -1， PREG_SPLIT_NO_EMPTY）;
如果 （计数（$pRunes） > 2） {
$sb .= $pRunes[2];
} 否则 {
$sb .= $sRunes[$i];
                }
破;
案例4：
$cRunes = preg_split（'//u'， $C， -1， PREG_SPLIT_NO_EMPTY）;
if （count（$cRunes） >= 4） {
$sb .= $cRunes[计数（$cRunes） - 4];
} 否则 {
$sb .= $sRunes[$i];
                }
破;
        }
    }

$base = $str;
if （（$idx = strpos（$str， “？”）） ！== false） {
$base = substr（$str， 0， $idx）;
    }

$dd = $sb;
$result = sprintf（“%s？%s&ddCalcu=%s”， $base， $parts['query']， $dd）;
返回$result;
}
?>
获取Migu Token方法如下
1、在电脑浏览器打开 
https://www.miguvideo.com/ 。
2、登录你的账号后按F12打开开发者工具，然后切换到Console标签页粘贴以下代码获取
（需要注意，migutv需要开通钻石VIP才能1080P,migu赛事需要开通对应体育或者NBA会员，否则你只能看到测试视频）：
function generateSubscriptionUrl() {
    const getCookie = (name) => {
        const value = '; ' + document.cookie;
        const parts = value.split('; ' + name + '=');
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
    };

    const user_info = JSON.parse(decodeURIComponent(getCookie('userInfo')));

    if (user_info) {
        let url;
        const user_id = user_info.userId;
        const user_token = user_info.userToken;
        if (user_id && user_token) {
            url = `http://你的IP:35455/miguevent.m3u?userid=${user_id}&usertoken=${user_token}`;
            console.log('你的订阅配置为：', url);
            return url;
        } else {
            console.log('用户Cookie缺失');
        }

    } else {
        console.log('用户Cookie找不到');
    }
}
generateSubscriptionUrl();
