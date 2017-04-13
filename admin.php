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
error_reporting(E_ERROR);
$db = get_private_db("monitor");

$project = isset($_GET['pj'])?" project = '$_GET[pj]'":"1=1";
$person = isset($_GET['pe'])?" person = '$_GET[pe]'":"1=1";

echo "<h1>Project</h1>";

$project = $db->get_list("select distinct(project) from monitor_project where $project and $person");

foreach($project as $one){
    $list = $db->get_list("select * from monitor_project where project='$one[project]' and $person");
    display_project($one['project'],$list);
}



function display_project($project,$list){
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


echo "<h2>$project</h2><table border=1><tr><th>Task</th><th>Person</th>";
foreach($all_date as $one){
    echo "<th>",substr($one,5),"</th>";
}
echo "<th>Edit</th></tr>";

foreach($list as $one){
    echo "<tr>";
    echo "<td><a href='?pj=$one[project]'>$one[task]</a></td><td><a href='?pe=$one[person]'>$one[person]</a></td>";
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

    echo "<td><a href='data/?datagrid_action=edit&var_name=gridObj&id=$one[id]' target=_blank>Edit</a></td></tr>";
}

echo "</table>";
echo "<a target=_blank href='index.php?pj=$project'>Display Project</a> ";
echo "<a href=export.php?pj=$project>Export</a><hr>";
echo "<a href=close.php?pj=$project>Close</a><hr>";
    
}



function add_date($orgDate,$mth=0,$day=0,$format='Y-m-d'){ 
  $cd = strtotime($orgDate);
  $retDAY = date($format, mktime(0,0,0,date('m',$cd)+$mth,date('d',$cd)+$day,date('Y',$cd))); 
  return $retDAY; 
}
function is_weekend($orgDate){ 
  $cd = strtotime($orgDate);  
  return date('w',$cd)==0 or date('w',$cd)==6; 
}

echo "<br>";
if(isset($_GET['pj'])){
    echo "<a target=_blank href='index.php?pj=$_GET[pj]'>Display Project</a><hr>";
    echo "<a href='data/?datagrid_action=search&datagrid_page=1&var_name=gridObj&id_exp=%3D&id_value=&project_exp=%3D&project_value=$_GET[pj]&task_exp=%3D&task_value=&person_exp=%3D&person_value=&detail_exp=%3D&detail_value=&start_at_exp=%3D&start_at_value=&end_at_exp=%3D&end_at_value=&hours_exp=%3D&hours_value=&percent_exp=%3D&percent_value=&Submit=%E6%8F%90%E4%BA%A4'>Manage Project</a>";
    
    echo "<br><a href=export.php?pj=$_GET[pj]>export</a>";
}elseif(isset($_GET['pe'])){
    echo "<a target=_blank href='index.php?pe=$_GET[pe]'>Display Person</a><hr>";
    echo "<a href='http://127.0.0.1/hpx/project//data/?datagrid_action=search&datagrid_page=1&var_name=gridObj&id_exp=%3D&id_value=&project_exp=%3D&project_value=&task_exp=%3D&task_value=&person_exp=%3D&person_value=$_GET[pe]&detail_exp=%3D&detail_value=&start_at_exp=%3D&start_at_value=&end_at_exp=%3D&end_at_value=&hours_exp=%3D&hours_value=&percent_exp=%3D&percent_value=&Submit=%E6%8F%90%E4%BA%A4'>Manage Person</a>";
    
    echo "<br><a href=export.php?pe=$_GET[pe]>export</a>";
}

echo "<br><a target=_blank href=upload.php>upload</a>";


