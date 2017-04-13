<?php
require("header.php");

require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);


$act = getParam('act');
$host = getParam('host','192.168.1.199');
$name = getParam('name','daniel');
$pass = getParam('pass','123456');
$db = getParam('db','jingrong');
$table = getParam('table');
	

function get_db2($host,$name,$pass,$db){
    $db = array($host,$name,$pass,$db, 'utf8');
    $db_host = $db[0];
    $db_user = $db[1];
    $db_pass = $db[2];
    $db_name = $db[3];    
    return new MysqlDAO($db_host, $db_user, $db_pass, $db_name);     
}

?>

<form method=post>
	<input type=hidden name=act value=conn>
	Host:<input type=text name=host value='<?php echo $host;?>'>
	User:<input type=text name=name value='<?php echo $name;?>'>
	Pass:<input type=password name=pass value='<?php echo $pass;?>'>
	DB:<input type=text name=db value='<?php echo $db;?>'>
	<input type=submit>

 
 
 <?php
 	 
$filerandom = "/sparkjobs/result_${db}_${table}_".date("Ymd_h_i_s") ;
if($act == 'conn'){
	$dblink = get_db2($host,$name,$pass,$db);  

	$tradingDayList = $dblink->get_list_h("show tables;",true);
	
	echo '<select name="table"   style="width:300px;"><option value="">--</option>';
	foreach($tradingDayList as $one){		
		$tablename = $one["Tables_in_$db"];		
		echo "<option value='$tablename'>$tablename</option>";
	}
	echo '</select>';
	
	
	$sql = <<<EOL
## Spark Application - execute with spark-submit 
# coding=utf-8
import sys
reload(sys)
sys.setdefaultencoding('utf-8')
 
## Imports 
import csv   
from StringIO import StringIO   
import time 
from datetime import datetime   
from collections import namedtuple   
from operator import add, itemgetter   
from pyspark import SparkConf, SparkContext   
from pyspark.sql import SQLContext     
from pyspark.sql.types import Row, StructField, StructType, StringType, IntegerType
   
## Module Constants 
APP_NAME = "Flight Delay Analysis" 
DATE_FMT = "%Y-%m-%d" 
TIME_FMT = "%H%M" 
    
fields   = ('ip','ua','ref','page','local_time','server_time') 
Flight   = namedtuple('Flight', fields) 


## Main functionality 
def main(sc): 
    # Read the CSV Data into an RDD 
    sqlContext = SQLContext(sc)  
    dataframe_mysql = sqlContext.read.format("jdbc").option("url", "jdbc:mysql://$host/$db").option("driver", "com.mysql.jdbc.Driver").option("dbtable", "$table").option("user", "$name").option("password", "$pass").option("characterEncoding","utf8").load()    
    dataframe_mysql.registerTempTable("trips")
    saverdd = sqlContext.sql("select * from trips")
    saverdd.map(lambda x: ','.join(map(lambda x: x.encode('utf-8') if type(x).__name__ == 'unicode' else str(x),x))).saveAsTextFile('hdfs://daniel:9000$filerandom')

## FUNCTIONS
def parse(row):     
    #Parses a row and returns a named tuple.   
    row[4] = float(row[4])
    row[5] = float(row[5])
    return Flight(*row[:6]) 
    
def split(line): 
    #Operator function for splitting a line with csv module 
    return line.split(' ')
    #reader = csv.reader(StringIO(line)) 
    #return reader.next() 
 
def ymd(server_time):
    return time.strftime("%Y-%m-%d", time.localtime(server_time))
 
def ymdhms(server_time):
    return time.strftime("%Y-%m-%d %H:%M:%S", time.localtime(server_time))
 
if __name__ == "__main__": 
    # Configure Spark 
    conf = SparkConf().setMaster("local[*]") 
    conf = conf.setAppName(APP_NAME) 
    sc   = SparkContext(conf=conf) 
    # Execute Main functionality 
    main(sc)
EOL;

	if($table){
	
	echo "</form target=_blank><form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='[import]$db.$table@$host' style='width:400px;'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='spark'>
            <input type=hidden name=saveas value='$filerandom'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>$sql</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
	
	$hive_table = "${table}_".date("Ymd_h_i_s");
	
	$fields_list = $dblink->get_list_h("SHOW COLUMNS FROM $table",true);
	$fields=array();	
	foreach($fields_list as $one){
		$fields[]=$one['Field']. ' string';	
	}
	$fields_str = implode(',',$fields);
	
	echo "</form target=_blank><form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='[create hive table] create tabel $hive_table using $filerandom'  style='width:400px;'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='sql'>
            <input type=hidden name=saveas value='$filerandom'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>use tang;
create external table if not exists $hive_table(
$fields_str
)
row format delimited fields terminated by ','
location '$filerandom';
set hive.cli.print.header=true;
select * from $hive_table limit 10;

</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
	
 
quit;	
	}
}	
?>