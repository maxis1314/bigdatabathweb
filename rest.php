<?php
exit;
require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);
header("Content-type: application/json; charset=utf-8");
$db = get_db('ionic'); 


$datastr = file_get_contents('php://input');
$postdata = json_decode($datastr,true);

$table = $postdata['t']?$postdata['t']:getParam('t');
$act = $postdata['act']?$postdata['act']:getParam('act');
$data =$postdata['data']?$postdata['data']:getParam('data'); 
if($act == "add"){	
	$result = $db->save($table,json_decode($data,true));
	die(json_encode(array('ret'=>$result?0:1,'debug'=>"result $table $act $data $result")));
}elseif($act == "delete"){	
	$result = $db->delete($table,$id);
	die(json_encode(array('ret'=>$result?0:1,'debug'=>"result $table $act $data $result")));
}elseif($act == "edit"){	
	$result = $db->update($table,$id,json_decode($data,true));
	die(json_encode(array('ret'=>$result?0:1,'debug'=>"result $table $act $data $result")));
}elseif($act == "find"){	
	$jobs = $db->find($table,json_decode($data,true));
	if(count($jobs)>0){
		die(json_encode(array('ret'=>0,'data'=>$jobs,'debug'=>"result $table $act $data")));
	}else{
		die(json_encode(array('ret'=>1,'debug'=>"result $table $act $data")));
	}
}elseif($act == "list"){	
	$jobs = $db->get_list_h("select * from $table",true);
	die(json_encode(array('ret'=>0,'data'=>$jobs)));
}
die(json_encode(array('ret'=>1,'debug'=>"result $table $act $data")));
