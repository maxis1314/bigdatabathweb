<?php
require __DIR__."/outerDB.php";
require __DIR__."/../login/auth.php";
error_reporting(E_ERROR);
$db = get_private_db("monitor");

$project = isset($_GET['pj'])?" project = '$_GET[pj]'":"1=1";
$person = isset($_GET['pe'])?" person = '$_GET[pe]'":"1=1";

$list = $db->get_list_h("select * from monitor_project where $project and $person");

header('Content-Type: application/csv');
header('Content-Disposition: attachement; filename="project.csv"');

echo implode(',',array_keys($list[0])),"\n";
foreach($list as $one){
    echo implode(',',array_values($one)),"\n";
}
