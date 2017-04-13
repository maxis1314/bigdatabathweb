<?php


$ji = $_GET['ji'];
$name = $_GET['name'];
$time = $_GET['time'];
$check = $_GET['check'];

$seed = "fdsfYfdsafHdLJFPIpdf";
if((time()-$time)>3600 || $check != md5($ji.$name.$time.$seed)){echo 1;exit;}


$file_dir = "/data/1_1/$ji/";

if (!file_exists($file_dir.$name)){
    header("Content-type: text/html; charset=utf-8");
    echo "File not found!";
    exit; 
} else {
    $file = fopen($file_dir.$name,"r"); 
    if(preg_match('/\.mp3$/',$name)){ 
        Header("Content-type: audio/mpeg");
    }else{
        Header("Content-type: application/octet-stream");
    }
    Header("Accept-Ranges: bytes");
    Header("Accept-Length: ".filesize($file_dir . $name));
    //Header("Content-Disposition: attachment; filename=".$name);
    echo fread($file, filesize($file_dir.$name));
    fclose($file);
}
