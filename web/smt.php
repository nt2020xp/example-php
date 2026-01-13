<?php
error_reporting(0); 
date_default_timezone_set("Asia/Shanghai");
function curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'stagefright/1.2 (Linux;Android 9)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
function get_path() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
}
function get_purl($host,$port,$id){
    $time = (int)floor((time() / 3600)/24);
    $seed = "tvata nginx auth module";
    $tid="mc42afe005811";
    $tsum  = md5($seed . "/{$id}/playlist.m3u8" . $tid . $time);
    $purl = "http://{$host}:{$port}/{$id}/playlist.m3u8?".http_build_query(["ct" => $time, "tsum" => $tsum,"tid"=>$tid]);
    return $purl;
}
function check_cache($path) {
    $targetPath = __DIR__ . DIRECTORY_SEPARATOR . $path;
    if (!is_dir($targetPath)) {
        if (mkdir($targetPath, 0777, true)) {
            return true;
        } else {
            return false;
        }
    }
    if (chmod($targetPath, 0777)) {
        return true;
    } else {
        return false;
    }
}
function update_category($cache_path){
    $api="http://tpshen.com:16310/index.php/live/api/type/1/mc42afe005811";
    $api_xml=simplexml_load_string(curl($api))??false;
    if($api_xml===false)
    return false;
    check_cache($cache_path);
    $json_path=__DIR__."{$cache_path}/#category.json";$json_data=[];
    foreach ($api_xml->apps->info as $info) {
        $app_id = $app_name_cn = $category_data=null;
        $app_id = (string)$info['app_id'];
        $app_name_cn = (string)$info['app_name_cn'];
        echo "获取成功:\n 国家/分类:{$app_name_cn} 区域ID:{$app_id}\n";
        $json_data[$app_id]=$app_name_cn;
        $category_data=curl("http://tpshen.com:16310/index.php/live/api/get/{$app_id}/mc42afe005811");
        if(strlen($category_data)>=300)
        if(file_put_contents(__DIR__."{$cache_path}/#{$app_id}.xml",$category_data)){
            echo "  成功：完成写入#{$app_id}.xml\n";
        }else{
            echo "  失败:未完成写入#{$app_id}.xml，请检查写入权限\n";
            
        }
}
    if(!empty($json_data))
    if(file_put_contents($json_path, json_encode($json_data, JSON_UNESCAPED_UNICODE))){
        echo "成功：完成写入json配置";
        }else{
            echo "失败:未完成写json配置，请检查写入权限";
   };
    return true;
}
function get_m3u($host,$port,$cache_path){
    $m3u="#EXTM3U x-tvg-url=".'"https://epg.iill.top/epg.xml.gz"'."\n";
    $json_data=json_decode(file_get_contents("./{$cache_path}/#category.json"),true)??false;
    if($json_data===false)
    return false;
    foreach ($json_data as $app_id =>$key){
        $category_data=simplexml_load_string(file_get_contents("./{$cache_path}/#{$app_id}.xml"));
        foreach ($category_data->content->info as $info){
            $tv_name=null;$matches=null;$id=null;$purl=null;
            if(preg_match("/:\d+\/(.*?)\//i",(string)$info['tv_url'],$matches)){
                $tv_name=(string)$info['tv_name'];
                $tv_log=preg_replace("/:\/\/.*?:\d+\//i","://tpshen.com:16310/",(string)$info['tv_logo']);
                $id=$matches[1];$purl=get_path()."?id={$id}&h={$host}&p={$port}";
                $m3u.='#EXTINF:-1 tvg-id="'.$id.'" tvg-name="'.$tv_name.'" tvg-logo="'.$tv_log.'" group-title="'.$key.'",'.$tv_name."\n$purl\n";
            }
        }
    }
    return $m3u;
}
$id = $_GET['id'] ?? '';
$host=$_GET['h'] ?? explode(':', $_SERVER['HTTP_HOST'])[0];
$port=$_GET['p'] ?? '22364';
$refresh=isset($argv[1]) ? $argv[1] : '';
$cache_path="/smt/chanel_config";
if(!empty($refresh)){
    update_category($cache_path);
    exit;
}
if(empty((trim($id)))){
    header('Content-Type: text/plain; charset=utf-8');
    die(get_m3u($host,$port,$cache_path));
}
$purl=get_purl($host,$port,$id);
header('Location:'.$purl);
