<?php
require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);

function create_file($path,$data){
	$handle = fopen($path, "w");
	fwrite($handle, $data);
	fclose($handle);
}

function getmicrotime() {
	list ($usec, $sec) = explode(' ', microtime());
	return ((float) $usec + (float) $sec);
}


$pwd = dirname(__FILE__);
while (true) {
	$mypid = getmypid();
	$db = get_db('jobs');
	 
	$lines = $db->get_list_h('select * from jobs where flag=0 order by id');
	if (count($lines) == 0) {
		echo date("Y-m-d h:i:s")." no job\n";
		break;	
	}
	$oneline = $lines[0];
	
	//check scale
	$lines_running = $db->get_list_h('select * from jobs where flag=1');
	if (count($lines_running) > 0) {
		echo date("Y-m-d h:i:s")." has job\n";
		break;	
	}		 

	$timenow = date("Y-m-d h:i:s");
	if(strstr($oneline["content"], '[[target]]')){
		$random = "result_$oneline[id]_".time();
		$target="/sparkjobs/$random";
		$oneline["target"]=$target;
		$oneline["content"]=str_replace('[[target]]',"hdfs://daniel:9000/sparkjobs/$random",$oneline["content"]);	
		$db->query("update jobs set flag=".EXECUTING.",target='$target',started_at='$timenow' where id=$oneline[id]");	
	}else{
		$db->query("update jobs set flag=".EXECUTING.",started_at='$timenow' where id=$oneline[id]");	
	}

    //0 waiting
    //1 executing
    //2 finished
    //3 unknow
    //99 ERROR
	if($oneline["type"] == "sql"){
		$filename = "$oneline[id].sql";		 
		create_file("$pwd/temp/$filename","set hive.cli.print.header=true;\n".$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/local/hive/bin/hive < $pwd/temp/$filename",$db,$oneline["id"]);
		$db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");		 
	}else if($oneline["type"] == "spark"){//hive sql
		$filename = "$oneline[id].py";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/local/spark/bin/spark-submit --jars  /usr/local/spark/lib/mysql-connector-java-5.1.6-bin.jar --jars /usr/local/spark/lib/spark-examples-1.6.1-hadoop2.6.0.jar $pwd/temp/$filename",$db,$oneline["id"]);			
        if(preg_match('/ERROR|Exception/',$result)){
            $db->query("update jobs set flag=".ERROR." where id=$oneline[id]");
        }else{
            $db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");
        }
	}else if($oneline["type"] == "php"){//hive sql
		$filename = "$oneline[id].py";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/bin/php $pwd/temp/$filename",$db,$oneline["id"]);			
        if(preg_match('/ERROR/i',$result)){
            $db->query("update jobs set flag=".ERROR." where id=$oneline[id]");
        }else{
            $db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");
        }
	}else if($oneline["type"] == "python"){//hive sql
		$filename = "$oneline[id].py";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/bin/python $pwd/temp/$filename",$db,$oneline["id"]);			
        if(preg_match('/ERROR/i',$result)){
            $db->query("update jobs set flag=".ERROR." where id=$oneline[id]");
        }else{
            $db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");
        }
	}else if($oneline["type"] == "file"){//hive sql
		$filename = "$oneline[id].txt";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/local/hadoop/bin/hadoop fs -mkdir -p /upload/$oneline[id]/;/usr/local/hadoop/bin/hadoop fs -put $pwd/temp/$filename /upload/$oneline[id]/; echo hdfs://daniel:9000/upload/$oneline[id]/;",$db,$oneline["id"]);			
        if(preg_match('/ERROR/i',$result)){
            $db->query("update jobs set flag=".ERROR." where id=$oneline[id]");
        }else{
            $db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");
        }
	}else if($oneline["type"] == "scala"){//hive sql
		$filename = "$oneline[id].scala";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/usr/local/spark/bin/spark-shell < $pwd/temp/$filename",$db,$oneline["id"]);
		$db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");		
	}else if($oneline["type"] == "cmd"){//bash files
		$filename = "$oneline[id].sh";		 
		create_file("$pwd/temp/$filename",$oneline["content"]);
		$result = execute_command("source /etc/bashrc;/bin/bash $pwd/temp/$filename",$db,$oneline["id"]);
		$db->query("update jobs set flag=".FINISHED." where id=$oneline[id]");	
	}else{
		$db->query("update jobs set flag=".UNKNOW." where id=$oneline[id]");
	}
    
    if($oneline["target"]){
        $result = execute_command_only("source /etc/bashrc;/usr/local/hadoop/bin/hadoop fs -getmerge $oneline[target] $pwd/temp/target_$filename;",$db,$oneline["id"]);
        $result = execute_command_only("source /etc/bashrc;cat $pwd/temp/target_$filename;",$db,$oneline["id"]);
		$db->update("jobs", $oneline['id'],array('stat'=>$result));		
    }
    
    $timenow = date("Y-m-d h:i:s");
	$db->query("update jobs set finished_at='$timenow' where id=$oneline[id]");
	break;	
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

function execute_command($cmd,$db,$job_id){
	$time_start = getmicrotime();
	$mypid = getmypid();
	$result = "CMD:$cmd\nPID : $mypid\n";
	if ($res = popen("$cmd 2>&1", "r")) {
		while (!feof($res)) {
			$progress = fgets($res, 1024);
			for($i=0;$i<10;$i++){
				$progress.=fgets($res, 1024);
			}
			$result .= $progress."\n";			
			$db->connect();			
			$db->update('jobs',$job_id,array('result'=>$result));							
			$count++;
		}
		pclose($res);
		
		$time = getmicrotime() - $time_start;
		$result.="\nCOST : $time s";		
		$db->connect();
		$db->update('jobs',$job_id,array('result'=>$result));
		file_put_contents(dirname(__FILE__).'/temp/log_job_'.$job_id,$result);
		return $result;
	}
	file_put_contents(dirname(__FILE__).'/temp/log_job_'.$job_id,$result);
	return "ERROR";
}
 
