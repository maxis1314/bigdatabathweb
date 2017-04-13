<?php
require("header.php");

require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);


$db = get_db('jobs');   

function get_type_select_html($type){
	$options = array("sql",'scala','spark','cmd');
	$str = "<select name=type>";
	foreach($options as $one){
		if($one != $type){
			$str.="<option value=$one>$one</option>";
		}else{
			$str.="<option value=$one selected>$one</option>";
		}
	}
	$str.="</select>";
	return $str;
}
        

$act=getParam('act');
$id=getParam('id');
$page=getParam('page')?getParam('page'):0;
if($act == "add_html"){
	if($id){
		$jobs = $db->get_list_h("select * from tables where id=$id",true);
		$job = $jobs[0];
	}
	echo "<form method=post action=jobs_manager.php><label>Title:</label><input type=text name=title value='".htmlspecialchars($job[title])."'><br>
			<input type=hidden name=act value='add'>
			<label>Type:</label>".get_type_select_html($job[type])."<br>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>".htmlspecialchars($job[content])."</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}


$jobs = $db->get_list_h("select * from tables  order by id desc limit $page,15",true);

?>
<script language="javascript">
window.onload = function () {
dp.SyntaxHighlighter.ClipboardSwf = 'public/SyntaxHighlighter/Scripts/clipboard.swf';
//function dp.SyntaxHighlighter.HighlightAll(name, [showGutter], [showControls],[collapseAll], [firstLine], [showColumns])
//dp.SyntaxHighlighter.HighlightAll('code');
//dp.SyntaxHighlighter.HighlightAll('code2',true,true,true);
<?php
foreach($jobs as $job){
	echo "dp.SyntaxHighlighter.HighlightAll('code$job[id]');";
	echo "dp.SyntaxHighlighter.HighlightAll('code2$job[id]');";
}
?>

}
</script>

<h1 class="page-header">JOBS LIST<small><a href=?act=add_html>[+]</a></small></h1>


<?php

if(count($jobs)==0){
	die('no more data');
}

if($page>0){
	echo "<a href=?page=".($page-5).">&lt;&lt;Newer</a> ";
}
echo "<a href=?page=".($page+5).">Older&gt;&gt;</a>";


echo "
<table border=1>";

foreach($jobs as $k=>$job){	
echo "<tr>
	<td>$job[title]</td><td>$job[type]</td><td>[ <a href=?act=add_html&id=$job[id]>Use</a>  / <a href=data2/t_tables.php?datagrid_action=view&var_name=gridObj&id=$job[id]>Show</a>  / <a href=data2/t_tables.php?datagrid_action=edit&var_name=gridObj&id=$job[id]>Edit</a> ]</td><tr>"; 
}


echo "
</table>";

echo "<br>";
if($page>0){
	echo "<a href=?page=".($page-5).">&lt;&lt;Newer</a> ";
}
echo "<a href=?page=".($page+5).">Older&gt;&gt;</a>";










require("footer.php");









?>


 






 
