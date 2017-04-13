<?php
require("header.php");

require("outerDB.php");
require("dbcenter.php");
require("lib/HiveDB.class.php");
error_reporting(E_ERROR);


$db = new HiveDB();   


$d = getParam('d');
$t = getParam('t');

$tables = $db->get_tables($d,$t);
$fields = $db->get_table_fields($d,$t);

$table_detail = $tables[0];
$table_detail[PARAM_VALUE] = $table_detail[PARAM_VALUE]?$table_detail[PARAM_VALUE]:'\x01';
$table_detail[LOCATION] = getParam('l') ? getParam('l') : $table_detail[LOCATION];

$act=getParam('act');



if($act == "addjob"){
	$groupby=getParam('groupby');
	$op=getParam('op');
	$target=getParam('target');
	$saveas=getParam('saveas');
    $seperator=getParam('seperator');
	
	$ranames = array();
	$rowstr = "";
	foreach($fields as $k=>$one){
		$ranames[]=$one['COLUMN_NAME'];
		if($one['TYPE_NAME']!='string' || $one['COLUMN_NAME']==$target){
			$rowstr.="    row[$k] = float(row[$k])\n";
		}	
	}
	$rowstr.="    return Flight(*row[:".count($fields)."])";
	
	$fieldsnames = "'".implode("','",$ranames)."'";
	
	if($saveas){
		$saveasstr = "saverdd.map(lambda x: '$seperator'.join(map(x.encode('utf-8') if type(x).__name__ == 'unicode' else str(x), x))).saveAsTextFile('$saveas')\n";
	}
	
	$ragroup = array();
	foreach($_POST as $k=>$one){
        if($one && preg_match('/^contains_/',$k)){
            $rk=substr($k,9);
            $ragroup[]="'$one' in f.$rk";
        }		
	}
	$groupstr = implode(" and ",$ragroup);
	
	$sql = <<<EOL
## Spark Application - execute with spark-submit  
  
## Imports  
import csv  
from StringIO import StringIO  
from datetime import datetime  
from collections import namedtuple  
from operator import add, itemgetter  
from pyspark import SparkConf, SparkContext  
  
## Module Constants  
APP_NAME = "Flight Delay Analysis"  
DATE_FMT = "%Y-%m-%d"  
TIME_FMT = "%H%M"  
   
fields   = ($fieldsnames)  
Flight   = namedtuple('Flight', fields)  
  
## Closure Functions  
def parse(row):      
    #Parses a row and returns a named tuple.    
$rowstr  
   
def split(line):  
    #Operator function for splitting a line with csv module  
    return line.split('$table_detail[PARAM_VALUE]')
    #reader = csv.reader(StringIO(line))  
    #return reader.next()  
  
  
## Main functionality  
def main(sc):  
# Read the CSV Data into an RDD  
    flights = sc.textFile("$table_detail[LOCATION]").map(split).map(parse)  
  
     
# Map the total delay to the airline (joined using the broadcast value)  
    saverdd  = flights.filter(lambda f: $groupstr)   
    $saveasstr
    delays  = saverdd.take(100) 
    print delays
   
if __name__ == "__main__":  
     
# Configure Spark  
    conf = SparkConf().setMaster("local[*]")  
    conf = conf.setAppName(APP_NAME)  
    sc   = SparkContext(conf=conf)  
  
     
# Execute Main functionality  
    main(sc)  
EOL;

	echo "<form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='[filter] $d.$t'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='spark'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>$sql</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}



?>


<h1 class="page-header">Create Filter JOB</h1>
 
<?php

echo "<form method=post>
<input type=hidden name='act' value='addjob'>
<input type=hidden name='d' value='$d'>
<input type=hidden name='t' value='$t'>
";

echo "<label>Filter By:</label>";
foreach($fields as $one){
	echo "$one[COLUMN_NAME] contains:<input type=text name=contains_$one[COLUMN_NAME]><br>";
}
$random = "${d}_${t}_".date("Ymd_h_i_s");
echo "<label>Save:</label>";
echo "<input type=text name=saveas value='[[target]]' style='width:620px;'>";
echo "<label>Seperator:</label>";
echo "<input type=text name=seperator value=','>";
echo "<input type=submit>
</form>";


require("footer.php");












 