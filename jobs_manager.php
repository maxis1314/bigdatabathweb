<meta http-equiv="refresh" content="300" />

<?php
require("header.php");

require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);


$db = get_db('jobs');   




function get_type_select_html($type){
	$options = array("sql",'scala','spark','cmd','php','python','file');
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
$keyword=getParam('keyword');
$page=getParam('page')?getParam('page'):0;
if($act == "redo"){
	$db->query("update jobs set flag=0,result='',stat='',started_at='',finished_at='' where id=$id");
	echo "job $id restarted!";
}elseif($act == "add_html"){
	if($id){
		$jobs = $db->get_list_h("select * from jobs where id=$id",true);
		$job = $jobs[0];
	}
	echo "<form method=post><label>Title:</label><input type=text name=title value='".htmlspecialchars($job[title])."'><br>
			<input type=hidden name=act value='add'>
			<label>Type:</label>".get_type_select_html($job[type])."<br>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>".htmlspecialchars($job[content])."</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";
}elseif($act == "add"){
	$title =getParam('title'); 
	$type =getParam('type'); 
	$content =getParam('content'); 
    $saveas =getParam('saveas');     
	$db->save('jobs',array('title'=>$title,'type'=>$type,'content'=>$content,'target'=>$saveas));
}elseif($act == "edit_html"){
	if($id){
		$jobs = $db->get_list_h("select * from jobs where id=$id",true);
		$job = $jobs[0];		
	}
	echo "<form method=post><label>Title:</label><input type=text name=title value='".htmlspecialchars($job[title])."'><br>
			<input type=hidden name=act value='edit'>
			<input type=hidden name=id value='$id'>
			<label>Type:</label>".get_type_select_html($job[type])."<br>
			<label>Content:</label><textarea name=content style='margin: 0px 0px 10px; width: 881px; height: 360px;'>".htmlspecialchars($job[content])."</textarea><br>			
				<input type=submit class='btn btn-success'>
			</form>";exit;
}elseif($act == "edit"){
	$title =getParam('title'); 
	$type =getParam('type'); 
	$content =getParam('content'); 
	$db->update('jobs',$id,array('title'=>$title,'type'=>$type,'content'=>$content));
}elseif($act == "kill"){
	$jobs = $db->get_list_h("select * from jobs where id=$id",true);
	$job = $jobs[0];
	preg_match('/PID : ([0-9]+)/i',$job['result'],$matches);
	if($matches[1]){
		$db->save('jobs',array('title'=>"kill job:$id pid:$matches[1]",'type'=>'cmd','content'=>"kill -9 $matches[1]"));
	}
	$db->update('jobs',$id,array('flag'=>99));
	$db->update('jobs',$id,array('flag'=>99));
}

$tid=getParam('tid');

if($tid){
	$showlog = 'display:block;';
}else{
	$showlog = 'display:none;';
}
$tid = $tid ? "id=$tid" : "1=1";
$keyword_sql = $keyword ? "title like '%$keyword%'" : "1=1";
$jobs = $db->get_list_h("select * from jobs where $tid and $keyword_sql order by last_updated desc limit $page,5",true);
$total = $db->get_list_h("select count(*) as num from jobs where $tid and $keyword_sql",true);


$page_array = array( 
  'total'     =>$total[0]['num'],
  'dispage'   =>5,
  'url' =>"?keyword=$keyword&page=",
  'now_page' =>$page,
); 
$pager =  front_page($page_array);

?>
<script type="text/javascript" src="highcharts/jquery-1.8.3.min.js"></script>
<script src="highcharts/highcharts.js"></script>
<script src="highcharts/exporting.js"></script>
	
	
<script type="text/javascript" src="public/syntax/scripts/shCore.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushJScript.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushPhp.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushPython.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushScala.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushSql.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushBash.js"></script>

<script type="text/javascript">SyntaxHighlighter.all();</script>
<link type="text/css" rel="stylesheet" href="public/syntax/styles/shCoreDefault.css"/>


<h1 class="page-header">JOBS LIST<small><a href=?act=add_html>[+]</a></small></h1>

<form><input type=text name=keyword><br><input type=submit value=search class="btn-warning btn"></form>
<?php

if(count($jobs)==0){
	die('no more data');
}

echo( $pager);


echo "
<table border=1>";

$job_status = array(
	WAITING=>'waiting',
	EXECUTING=>'executing',
	FINISHED=>'done',
    UNKNOW=>'unknow',
    ERROR=>'error'
);

$jobfirst = true;
foreach($jobs as $k=>$job){
	$job[flag] = $job_status[$job[flag]];
echo "<tr>
	<td><h2><a href=?tid=$job[id]>";
	if($job[flag]=="waiting"){
		echo "<font color=SkyBlue>$job[title]</font>";
	}elseif($job[flag]=="done"){
		echo "<font color=grey>$job[title]</font>";
	}elseif($job[flag]=="executing"){
		echo "<font color=Green>$job[title]</font>";
	}elseif($job[flag]=="unknow"){
		echo "<font color=red>$job[title]</font>";
	}elseif($job[flag]=="error"){
		echo "<font color=red>$job[title]</font>";
	}
	
	echo "</a> <small>JOB ID : $job[id] [<a href=?act=redo&id=$job[id]>Redo</a>/<a href=?act=add_html&id=$job[id]>Reuse</a>/<a href=?act=edit_html&id=$job[id]>Edit</a>/<a href=?act=kill&id=$job[id]>Kill</a>]</small></h2>$job[type]/$job[flag]/$job[last_updated]
    ($job[started_at] ~ $job[finished_at])
    ".(strtotime($job[finished_at])-strtotime($job[started_at])).' s<br>';
    
    if($job['stat']){
    	$lines = explode("\n",$job[stat]);
    	$final = array_slice($lines,0,10);
    	echo "<pre class='brush: js;'>".implode("\n",$final)."</pre>";
    	echo "<font size=1><a href=result_download.php?id=$job[id]&col=stat>Download Result</a> / <a href=result_download.php?id=$job[id]&col=result>Download Log</a></font><br>";
        $array = explode("\n",$job['stat']);
        $line = explode(",",$array[0]);
        $cols = count($line);
        $resultstat = array();
        for($i=1;$i<$cols;$i++){            
            foreach($array as $one){
                $line2 = explode(",",$one);
                if(count($line2)!=$cols){
                    continue;
                }
                $resultstat[] = array('x'=>$line2[0],'y'=>"col-$i",'v'=>floatval($line2[$i]));
            }
            //echo show_bars($resultstat,'JOB History','Day','Num','1 days','%m-%d',900,240);            
        }
        echo show_lines_hc($resultstat,"STAT",'','Num',$width,$height);        
    }

	$mapping = array(
		'spark'=>'python',
		'cmd'=>'bash',
		'file'=>'js',
	);
	$hightligher = $mapping[$job[type]] ? $mapping[$job[type]]: $job[type];
	
	echo "
<input type=button onclick='$(\"#detail$k\").toggle();' value=show><br>
    <div id='detail$k' style='$showlog'>
    
	    <pre class='brush: $hightligher;'>",htmlspecialchars($job[content]),"</pre>
    	
     
        
	<h2><a target=_blank href='http://192.168.1.199:50070/explorer.html#$job[target]'>Result:</a></h2>
		<pre class='brush: bash;'>TGT:$job[target]\n$job[result]</pre>
</div>";

    
    
   
    echo "
	<a href=?act=redo&id=$job[id]>Redo</a>/<a href=?act=add_html&id=$job[id]>Reuse</a>/<a href=?act=edit_html&id=$job[id]>Edit</a><br><br></div></td></tr>";
}


echo "
</table>";

echo "<br>";
echo( $pager);








require("footer.php");









?>


 <center>

 <?php
  $jobstat = $db->get_list_h("select DATE_FORMAT(last_updated,'%m-%d-%Y') as x,type as y,count(*) as v FROM jobs.jobs group by DATE_FORMAT(last_updated,'%m-%d-%Y'),type",true);

  //echo show_bars($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
  //echo show_lines($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
  echo show_lines_hc($jobstat,"TYPE",'','Num',$width,$height);    
  
  $jobstat = $db->get_list_h("select DATE_FORMAT(last_updated,'%m-%d-%Y') as x,flag as y,count(*) as v FROM jobs.jobs group by DATE_FORMAT(last_updated,'%m-%d-%Y'),flag",true);

  //echo show_bars($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
  //echo show_lines($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
  echo show_lines_hc($jobstat,"FLAG",'','Num',$width,$height); 
 ?>


 </center> 

<?php



function front_page( $page_array ){ 
 
    //分页判断 
    $lastpage = ceil($page_array['total'] / $page_array['dispage']); //最后一页 
    $page_array['now_page'] = min( $lastpage , $page_array['now_page'] );//比较当前页数和最后一页 
    $prepage = $page_array['now_page'] - 1;//上一页 
    $nextpage = ($page_array['now_page'] == $lastpage ? 0 : $page_array['now_page'] + 1 );//下一页 
    $firstcount = ($page_array['now_page']-1) * $page_array['dispage']; 
    if( $lastpage <= 1) return false;//最后一页小于1，则直接返回 
 
    //首页 
    if($prepage){ 
        $page_array_link[]="<a href='{$page_array['url']}"."1'>首页</a>"; 
    }else{ 
        $page_array_link[]="<span class=\"nolink\">首页</span>"; 
    } 
    //上一页 
    if($prepage) { 
        $page_array_link[]="<a  href='{$page_array['url']}$prepage'> 上一页 </a> "; 
    }else{ 
        $page_array_link[]=''; 
    } 
 
    //显示的数字分页条数 
    $pagenum=5; 
    $offset=2;//偏移两 
    $from=$page_array['now_page'] - $offset;//起始 
    $to=$page_array['now_page'] + $pagenum-$offset-1;//终止 
    if($pagenum>$lastpage){ 
        $from=1; 
        $to=$lastpage; 
    }else{ 
        if($from<1){ 
            $to=$page_array['now_page'] + 1 - $from; 
            $from=1; 
            if(($to-$from)<$pagenum && ($to-$from)<$lastpage){ 
                $to=$pagenum; 
            } 
        }elseif($to>$lastpage){ 
            $from=$page_array['now_page'] - $lastpage+$to; 
            $to=$lastpage; 
            if(($to-$from)<$pagenum && ($to-$from)<$lastpage){ 
                $from=$lastpage-$pagenum+1; 
            } 
        } 
    } 
    for($i=$from;$i<=$to;$i++){ 
        if($i == $page_array['now_page']){ 
            $page_array_link[]=" $i "; 
        }else{ 
            $page_array_link[]="<a href=\"{$page_array['url']}$i\" title=\"\">$i</a> "; 
 
        } 
    } 
    //数字分页 
    $page_array[]= $pagenav; 
    //下一页 
    if($nextpage){ 
        $page_array_link[]="<a href='{$page_array['url']}"."$nextpage'> 下一页 </a> "; 
    }else { 
        $page_array_link[]=''; 
    } 
    //尾页 
    if($nextpage){ 
        $page_array_link[]="<a href='{$page_array['url']}"."$lastpage'>尾页</a>"; 
    }else{ 
        $page_array_link[]='<span class="nolink">尾页</span>'; 
    } 
    //记录数 
    $page_array_link[]="<span>每页   ".$page_array['dispage']."  条</span> <span>共  ".$page_array['total']."  条纪录</span>"; 
    //分页数 
    $page_array_link[]="共  ".$lastpage." 页"; 
 
    //跳页 
    $page_array_link[] = '<span>跳转到：</span><input size="4" id="pagenu" type="text" onkeydown="javascript：if(event.keyCode==13){var page=(this.value<0)?1:this.value;location=\''.$page_array['url'].'\'+page;}" />
                    <a href="javascript:void(0)" onclick="javascript:var spage=(window.document.getElementById(\'pagenu\').value==\'\')?1:window.document.getElementById(\'pagenu\').value;location=\''.$page_array['url'].'\'+spage;">Go</a>'; 
    //第一页总数 
    $firstcount=$firstcount < 1 ? 0 : $firstcount; 
    return implode(' ',$page_array_link);
    return array('pagenav'=>$page_array,'limit'=>$firstcount,'offset'=> $page_array['dispage'],'allpage'=>$lastpage); 
}





 