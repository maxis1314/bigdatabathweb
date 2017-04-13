<?php
require("header.php");

require("outerDB.php");
require("dbcenter.php");
require("lib/HiveDB.class.php");
error_reporting(E_ERROR);


$db = new HiveDB();   



$act=getParam('act');
$id=getParam('id');
if($act == "addtable"){
	$dbname=getParam('db');
	$table=getParam('table');
	$location=getParam('location');
	$fields=getParam('fields');
	$separator=getParam('separator');
	
	$sql = "use $dbname;\ncreate external table if not exists $table(
$fields
)
row format delimited fields terminated by '$separator'
location '$location';\n\nquit;\n\n";

	echo "<form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='create table $dbname.$table'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='sql'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 180px;'>$sql</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}else if($act == "deletetable"){
	$dbname=getParam('d');
	$table=getParam('t');
	$sql = "use $dbname;\ndrop table $table;\n\n";
	echo "<form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='drop table $dbname.$table'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='sql'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 180px;'>$sql</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}



$page=getParam('page')?getParam('page'):0;
$dbname=getParam('d')?"NAME='".getParam('d')."'":"1=1";
$tblname=getParam('t')?"TBL_NAME='".getParam('t')."'":"1=1";

$tables_all = $db->get_all_tables();

$tables = $db->get_tables(getParam('d'),getParam('t'));

$fields = $db->get_table_fields(getParam('d'),getParam('t'));

?>


<h1 class="page-header">TABLES LIST</h1>
<script>
function search(index){
//	alert(document.getElementById('tableidx').value);
	window.location = document.getElementById('tableidx').value;
}
function deletetable(index){
	//alert(document.getElementById('tableidx2').value);
	window.location = document.getElementById('tableidx2').value;
}
</script>


<?php
echo "<select id='tableidx' onChange='search(this.selectedIndex);'><option>--</option>";
foreach($tables_all as $one){
	echo "<option value='?d=$one[NAME]&t=$one[TBL_NAME]&l=",urlencode($one['LOCATION']),"'>[",$one['NAME'],'.',$one['TBL_NAME'],"] ",$one['LOCATION'],"</option>";
}
echo "</select>";

if(getParam('d')&&getParam('t')){
	echo "<br>";
	echo "<a href=create_pyjobs.php?d=".getParam('d')."&t=".getParam('t')."&l=".getParam('l').">Create PYTHON Analysis</a><br>";
	echo "<a href=create_sqljobs.php?d=".getParam('d')."&t=".getParam('t')."&l=".getParam('l').">Create SQL Analysis</a><br>";
    echo "<a href=create_pyjobs_filter.php?d=".getParam('d')."&t=".getParam('t')."&l=".getParam('l').">Create Filter</a><br>";
}

echo "
<table border=1>";

print_table('Tables',$tables,count($tables));
print_table('Fields',$fields,count($fields));



?>
<h3>Add Table</h3>
<form method=post>
<input type=hidden name='act' value='addtable'>
DB:<input type=text name=db value='tang'>
Table:<input type=text name=table>
Location:<input type=text name=location placeholder='/ontime/flights/'>
Fields:<input type=text name=fields placeholder='a string, b int'>
Separator:<input type=text name=separator value=','>
<input type=submit>
</form>
	
	
	<h3>Drop Table</h3>
Table:
		<?php
echo "<select id='tableidx2' onChange='deletetable(this.selectedIndex);'><option>--</option>";
foreach($tables_all as $one){
	echo "<option value='?act=deletetable&d=$one[NAME]&t=$one[TBL_NAME]'>[",$one['NAME'],'.',$one['TBL_NAME'],"] ",$one['LOCATION'],"</option>";
}
echo "</select>";


require("footer.php");












 