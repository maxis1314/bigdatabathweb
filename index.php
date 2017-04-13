<meta charset="utf-8">
<style>
body{
    font-family:"微软雅黑";   
}
table {
    border-collapse: collapse;
}

table, td, th {
    border: 1px solid black;
}
.occupy{
    background:lightgreen;
}
.weekend{
    background:lightgrey;
}
</style>

<?php
require __DIR__."/outerDB.php";
require __DIR__."/../login/auth.php";
$db = get_private_db("monitor");

$project = isset($_GET['pj'])?" project = '$_GET[pj]'":"1=1";
$person = isset($_GET['pe'])?" person = '$_GET[pe]'":"1=1";

if($project== "1=1" and $person=='1=1'){
    die('no input');
}

echo "<h1>Project</h1>";

$list = $db->get_list("select * from monitor_project where $project and $person");

$date_array = array();
foreach($list as $one){
    $date_array[]=$one['start_at'];
    $date_array[]=$one['end_at'];
}

$first_date = min($date_array);
$last_date=max($date_array);

$all_date = array();
$date = $first_date;
while($date<=$last_date){
    $all_date[]=add_date($date,0,0);
    $date=add_date($date,0,1);
}
echo "<table border=1><tr><th>Task</th><th>Person</th>";
foreach($all_date as $one){
    echo "<th>",substr($one,5),"</th>";
}
echo "</tr>";

foreach($list as $one){
    echo "<tr>";
    echo "<td>$one[task]</td><td>$one[person]</td>";
    foreach($all_date as $date){
        if(is_weekend("$date 00:00:00")){
            echo "<td class=weekend></td>";
        }else{
            if($date>=substr($one['start_at'],0,10) && $date<=substr($one['end_at'],0,10)){
                echo "<td class=occupy></td>";
            }else{
                echo "<td></td>";
            }
        }
    }

    echo "</tr>";
}

echo "</table>";




function add_date($orgDate,$mth=0,$day=0,$format='Y-m-d'){ 
  $cd = strtotime($orgDate);
  $retDAY = date($format, mktime(0,0,0,date('m',$cd)+$mth,date('d',$cd)+$day,date('Y',$cd))); 
  return $retDAY; 
}
function is_weekend($orgDate){ 
  $cd = strtotime($orgDate);  
  return date('w',$cd)==0 or date('w',$cd)==6; 
}


