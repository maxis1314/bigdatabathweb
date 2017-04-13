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
    $groupstr=getParam('groupstr');
    $needstat=getParam('needstat');
	
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
		if($op=='add'){
			$saveasstr = "saverdd.map(lambda x: '$seperator'.join(map(lambda x: x.encode('utf-8') if type(x).__name__ == 'unicode' else str(x), x))).saveAsTextFile('$saveas')\n";			
		}else if($op=='add_array'){
			$saveasstr = "saverdd.map(lambda x: x[0]+','+'$seperator'.join(map(lambda x: x.encode('utf-8') if type(x).__name__ == 'unicode' else str(x), x[1]))).saveAsTextFile('$saveas')\n";
		}
	}
	
	/*$ragroup = array();
	foreach($groupby as $one){
		$ragroup[]="f.$one";
	}
	$groupstr = implode("+",$ragroup);
    $groupstr = $groupstr?$groupstr:"'C'";*/
    
    
    $filtergroup = array();
	foreach($_POST as $k=>$one){
        if($one && preg_match('/^contains_/',$k)){
            $rk=substr($k,9);
            $filtergroup[]="'$one' in f.$rk";
        }		
	}
	$filtergroupstr = implode(" and ",$filtergroup);
    if($filtergroupstr){
        $filtergroupstr = "flights  = flights.filter(lambda f: $filtergroupstr) ";
    }
	
	
	if($needstat && $op!='add_array'){
		$needstatstr = <<<EOL1
purenum = saverdd.map(lambda x:x[1])
    CNT = purenum.count()
    AVG = purenum.mean()
    SUM = purenum.sum()
    MIN = purenum.min()
    MAX = purenum.max()
    FANGCHA = purenum.variance()
    SAMPLE_FANGCHA = purenum.sampleVariance()
    BIAO_ZHUN_CHA = purenum.stdev()
    SAMPLE_BIAO_ZHUN_CHA = purenum.sampleStdev()
 
    print "CNT:  %f" % CNT
    print "AVG :  %0.4f" % AVG
    print "SUM: %f " % SUM
    print "MIN~MAX: %f  ~ %f " % (MIN,MAX)
    print "Variance:  %0.1f" % FANGCHA    
    print "Stdev:  %0.1f " % BIAO_ZHUN_CHA
    print "SAMPLE Variance:  %0.1f" % SAMPLE_FANGCHA
    print "SAMPLE Stdev:  %0.1f" %SAMPLE_BIAO_ZHUN_CHA


EOL1;
	}
	
	$sql = <<<EOL
## Spark Application - execute with spark-submit  
  
## Imports  
import csv
import numpy
from StringIO import StringIO  
import time
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
  


  
## Main functionality  
def main(sc):  
    flights = sc.textFile("$table_detail[LOCATION]").map(split).map(parse)  
    $filtergroupstr
    delays  = flights.map(lambda f: ($groupstr,$target))

    # Reduce the total delay for the month to the airline 
    saverdd = delays.reduceByKey($op)
    saverdd.map(lambda x: x[0]+','+','.join(map(lambda x: x.encode('utf-8') if type(x).__name__ == 'unicode' else str(x), x[1]))).saveAsTextFile('[[target]]')
 
    delays  = saverdd.collect()
    print delays 
    delays  = sorted(delays, key=lambda x:x[1], reverse=True) 
    #delays  = sorted(delays, key=itemgetter(1), reverse=True) 
    print delays 
    for each in saverdd.collect():     
        print(each) 
 
    $needstatstr
 
 
    
## FUNCTIONS
def parse(row):     
    #Parses a row and returns a named tuple.   
$rowstr
    
def split(line): 
    #Operator function for splitting a line with csv module 
    return line.split(',')
    #reader = csv.reader(StringIO(line)) 
    #return reader.next() 
 
def add_array(x,y):
    if len(x)!=len(y):
        return x
    z = [x,y]
    g = numpy.sum(z,axis=0)
    return tuple(g.tolist())
 
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

	echo "<form method=post action='jobs_manager.php'><label>Title:</label><input type=text name=title value='[pyspark]$d.$t $op($target) BY $groupstr'><br>
			<input type=hidden name=act value='add'>
			<input type=hidden name=type value='spark'>
            <input type=hidden name=saveas value='$saveas'>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>$sql</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}



?>


<h1 class="page-header">Create PYTHON JOB</h1>
 
<?php

echo "<form method=post>
<input type=hidden name='act' value='addjob'>
<input type=hidden name='d' value='$d'>
<input type=hidden name='t' value='$t'>

<label>LOCATION:</label>
<input type=text name=l value='$table_detail[LOCATION]'  style='width:620px;'><br>

";

echo "<label>Filter By:</label>";
foreach($fields as $one){
	echo "$one[COLUMN_NAME] contains:<input type=text name=contains_$one[COLUMN_NAME]>";
}

echo "<label>Group By:</label>";
foreach($fields as $one){
	//if($one['TYPE_NAME']!='string'){
		echo "<input type=radio name=fields value='$one[COLUMN_NAME]' onclick='$(\"#groupstr\").val(\"f.$one[COLUMN_NAME]\");'>$one[COLUMN_NAME] ";
	//}
}
echo "<input id=groupstr type=text name=groupstr>";
echo "<label>Operation:</label>";
echo "<select name=op><option value=add>add</option><option value=add_array>add array</option></select>";

echo "<label>Target:</label>";
foreach($fields as $one){
	//if($one['TYPE_NAME']!='string'){
		echo "<input type=radio name=fields value='$one[COLUMN_NAME]' onclick='$(\"#target\").val(\"f.$one[COLUMN_NAME]\");'>$one[COLUMN_NAME] ";
	//}
}
echo "<input id=target type=text name=target>";

echo "<label>Save:</label>";
$random = "${d}_${t}_".date("Ymd_h_i_s");
echo "<input type=text name=saveas value='[[target]]' style='width:620px;'>";
echo "<label>Seperator:</label>";
echo "<input type=text name=seperator value=','>";
echo "<label><input type=checkbox name=needstat value='1'> Need Stat</label>";
echo "<input type=submit>
</form>";


require("footer.php");












 