<?php
error_reporting(E_ERROR);

for($i=-1;$i>-7;$i--){
	$date = date("Y-m-d",time()+$i*24*3600);
	echo $date;
	if(file_exists("/home/tmp/$date")){echo "exists $date\n";continue;}
	$str = file_get_contents("http://115.28.24.177:8092/sts/get_counter.php?p=321&d=$i");
	echo "http://115.28.24.177:8092/sts/get_counter.php?p=321&d=$i\n";
	if($str){
		$fp = fopen("/home/tmp/$date","w");
		fwrite($fp,$str);
		fclose($fp);
		echo "save $date\n";
        echo execute_command_only("source /etc/bashrc;/usr/local/hadoop/bin/hadoop fs -mkdir /accesslog/$date; /usr/local/hadoop/bin/hadoop fs -put /home/tmp/$date /accesslog/$date/; /usr/local/hive/bin/hive -e \"load data inpath '/accesslog/$date/' into table tang.p_access_log partition(date='$date')\"");
	}
}

function execute_command_only($cmd){	
	$result = "";
	if ($res = popen("$cmd 2>&1", "r")) {
		while (!feof($res)) {
			$progress = fgets($res, 1024);
			for($i=0;$i<10;$i++){
				$progress.=fgets($res, 1024);
			}
			$result .= $progress."\n";			
		}
		pclose($res);
		return $result;
	}
	return "FAILED:";
}
