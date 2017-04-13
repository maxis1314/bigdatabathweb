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
$page=getParam('page')?getParam('page'):0;
if($act == "redo"){
	$db->query("update jobs set flag=0,result='' where id=$id");
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
			</form>";
}elseif($act == "edit"){
	$title =getParam('title'); 
	$type =getParam('type'); 
	$content =getParam('content'); 
	$db->update('jobs',$id,array('title'=>$title,'type'=>$type,'content'=>$content));
}

$tid=getParam('tid');
if($tid){
	$showlog = 'display:block;';
}else{
	$showlog = 'display:none;';
}
$tid = $tid ? "id=$tid" : "1=1";
$jobs = $db->get_list_h("select * from jobs where $tid  order by last_updated desc limit $page,5",true);

?>

<script type="text/javascript" src="public/syntax/scripts/shCore.js"></script>
<script type="text/javascript" src="public/syntax/scripts/shBrushJScript.js"></script>
<script type="text/javascript">SyntaxHighlighter.all();</script>
<link type="text/css" rel="stylesheet" href="public/syntax/styles/shCoreDefault.css"/>


	
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
	
	echo "</a> <small>JOB ID : $job[id] [<a href=?act=redo&id=$job[id]>Redo</a>/<a href=?act=add_html&id=$job[id]>Reuse</a>/<a href=?act=edit_html&id=$job[id]>Edit</a>]</small></h2>$job[type]/$job[flag]/$job[last_updated]
    
    ";
    
    if($job['stat']){
    	echo "<pre class='brush: js;'>$job[stat]</pre>";
        $array = explode("\n",$job['stat']);
        $line = explode(",",$array[0]);
        $cols = count($line);
        for($i=1;$i<$cols;$i++){
            $resultstat = array();
            foreach($array as $one){
                $line2 = explode(",",$one);
                if(count($line2)!=$cols){
                    continue;
                }
                $resultstat[] = array('label'=>$line2[0],'num'=>$line2[$i]);
            }
            echo show_bars($resultstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
        }
    }

	echo "
<input type=button onclick='$(\"#detail$k\").toggle();' value=show><br>
    <div id='detail$k' style='$showlog'>
    
    <div class='limit_width'><textarea style='*word-wrap: break-word;'
			name='code$job[id]' class='c-sharp' cols='95' rows='10'>$job[content]</textarea>
        </div>
        
	<h2>Result:</h2>
	<div class='limit_width'><textarea style='*word-wrap: break-word;'
			name='code2$job[id]' class='c-sharp' cols='95' rows='10'>$job[result]</textarea>
</div>";

    
    
   
    echo "
	<a href=?act=redo&id=$job[id]>Redo</a>/<a href=?act=add_html&id=$job[id]>Reuse</a>/<a href=?act=edit_html&id=$job[id]>Edit</a><br><br></div></td></tr>";
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


 <center>

 <?php
  $jobstat = $db->get_list_h("select DATE_FORMAT(last_updated,'%m-%d-%Y') as label,count(*) as num FROM jobs.jobs group by DATE_FORMAT(last_updated,'%m-%d-%Y')",true);

  echo show_bars($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
  echo show_lines($jobstat,'JOB History','Day','Num','1 days','%m-%d',900,240);
 ?>


 </center> 







 