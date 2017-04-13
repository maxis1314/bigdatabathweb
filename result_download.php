<?php
error_reporting(E_ERROR);
require("outerDB.php");
require("dbcenter.php");
header("Content-type:application/txt");
//header("Content-Disposition:attachment;filename='downloaded.pdf'");

$db = get_db('jobs');   
$col = getParam('col')?getParam('col'):'result';
 
$id=getParam('id');
header("Content-Disposition:inline;filename='job-$id-$col.txt'");
$jobs = $db->get_list_h("select * from jobs where id=$id",true);
$job = $jobs[0];
echo $job[$col];
exit;
