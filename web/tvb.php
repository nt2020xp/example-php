<？php
/*
* GeJI恩山論壇
*.php？id=0 無線新聞台[1280x720]
*.php？id=1 無線財*體育資訊台[1280x720]
*.php？id=2 無線新聞台·海外版[1920x1080]
*.php？id=3 無線財*體育資訊台·網络版[1920x1080]
*.php？id=4 事件直播1台[1280x720]
*.php？id=5 事件直播2台[1280x720]
*.php？id=6 無線新聞台[max1920x1080，min960x360，多畫質多音軌DIYP不支援]
*.php？id=7 無線財*體育資訊台[max1920x1080，min960x360，多畫質多音軌DIYP不支援]
*.php？id=8 事件直播1台[max1920x1080，min960x360，多畫質多音軌DIYP不支援]
*.php？id=9 事件直播2台[max1920x1080，min960x360，多畫質多音軌DIYP不支援]
*/
$id = $_GET['id'];
$ids = ['C'，'A'，'I-NEWS'，'I-FINA'，'NEVT1'，'NEVT2'，'C'，'A'，'NEVT1'，'NEVT2'];
$header[] = '用戶端 IP：'.$_SERVER['REMOTE_ADDR'];
$header[] = 'X-FORWARDED-FOR：'.$_SERVER['REMOTE_ADDR'];
$ch = curl_init（）;
curl_setopt（$ch，CURLOPT_URL，'https：//inews-api.tvb.com/news/checkout/live/hd/ott_'.$ids[$id].'_h264？profile=safari'）;
curl_setopt（$ch，CURLOPT_HTTPHEADER，$header）;
curl_setopt（$ch，CURLOPT_RETURNTRANSFER，1）;
curl_setopt（$ch，CURLOPT_SSL_VERIFYPEER，false）;
curl_setopt（$ch，CURLOPT_SSL_VERIFYHOST，false）;
$data = curl_exec（$ch）;
curl_close（$ch）;
$json = json_decode（$data）;
if（$id == '0' || $id == '1' || $id == '4' || $id == '5'） {
$url = $json->內容->url->高清;
} else if（$id == '2' || $id == '3'） {
$url = preg_replace（'/&p=（.*？）$/'，'&p=3000'，$json->內容->url->高清）;
} 否則 {
$url = preg_replace（'/&p=（.*？）$/'，''，$json->content->url->hd）;
};
標頭（'位置：'.$url）;
