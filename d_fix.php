<?php
require('english_common.php');

$ji = $_GET['ji'];
$name = $_GET['name'];
$time = $_GET['time'];
$check = $_GET['check'];

if((time()-$time)>3600 || $check != md5($ji.$name.$time.$seed)){exit;}


$bookdir = "$datadir/1_1/$ji/";

$data = file_get_contents($bookdir.findFile($bookdir,'txt'));
$data2 = json_decode($data,true);

$ans = array();
$url = "";
foreach($data2["Listening"] as $one){
    $audio = $one['audio'];
    $images = $one['images'];

    foreach($audio as $k=>$two){
        $two['src']=trim($two['src']);
        $a = basename($two['src']);

        if($a == $name){
            $url= $two['src'];
        }

        $two = $images[$k];
        $two['src']=trim($two['src']);
        $b = basename($two['src']);

        if($b == $name){
            $url= $two['src'];
        }
    }
}

if($url){
    echo "http://oss-nocdn.dasijiaoyu.com".$url,":",basename($url),":",$bookdir;
    GrabImage("http://oss-nocdn.dasijiaoyu.com".$url,basename($url),$bookdir);
}

function GrabImage($url,$filename,$dir='pic1') { 
 //if(file_exists("$dir/$filename")){return;}echo "get $url\n";
 if($url==""):return false;endif; 
 
 ob_start(); 
 readfile($url); 
 $img = ob_get_contents(); 
 ob_end_clean(); 
 $size = strlen($img); 
 
 //"../../images/books/"为存储目录，$filename为文件名
 $fp2=@fopen("$dir/".$filename, "w"); 
 fwrite($fp2,$img); 
 fclose($fp2); 
 echo "saved $dir/$filename\n\n";
 
 return $filename; 
} 