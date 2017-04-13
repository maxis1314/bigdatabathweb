<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/jquery-ui-1.10.0.custom.css" rel="stylesheet" />
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/font-awesome.min.css"  rel="stylesheet">
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/prettify.css"  rel="stylesheet">
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/bootstrap/css/flat-ui.css" rel="stylesheet" media="screen">
<link href="http://115.28.24.177:8092/myadmin/frontweb/application/views/static/css/style.css" rel="stylesheet" media="screen">
<style>
body{
margin:20px;
}
</style>

<style type="text/css"> 
<!-- 
body,div,ul,li{ 
padding:0; 
text-align:center; 
} 
body{ 
font:12px "微软雅黑"; 
text-align:center; 
} 
a:link{ 
color:#00F; 
text-decoration:none; 
} 
a:visited { 
color: #00F; 
text-decoration:none; 
} 
a:hover { 
color: #c00; 
text-decoration:underline; 
} 
ul{ list-style:none;} 
/*选项卡1*/ 
#Tab1{ 
#width:90%; 
margin:0px; 
padding:0px; 
margin:0 auto;} 
/*选项卡2*/ 
#Tab2{ 
width:576px; 
margin:0px; 
padding:0px; 
margin:0 auto;} 
/*菜单class*/ 
.Menubox { 
width:100%; 
background:url(http://www.jb51.net/upload/small/20079299441652.gif); 
height:28px; 
line-height:28px; 
} 
.Menubox ul{ 
margin:0px; 
padding:0px; 
} 
.Menubox li{ 
float:left; 
display:block; 
cursor:pointer; 
width:114px; 
text-align:center; 
color:#949694; 
font-weight:bold; 
} 
.Menubox li.hover{ 
padding:0px; 
background:#fff; 
width:116px; 
border-left:1px solid #A8C29F; 
border-top:1px solid #A8C29F; 
border-right:1px solid #A8C29F; 
background:url(http://www.jb51.net/upload/small/200792994426548.gif); 
color:#739242; 
font-weight:bold; 
height:27px; 
line-height:27px; 
} 
.Contentbox{ 
clear:both; 
margin-top:0px; 
border:1px solid #A8C29F; 
border-top:none; 
height:181px; 
text-align:center; 
padding-top:8px; 
} 
--> 
</style> 
<script> 
<!-- 
/*第一种形式 第二种形式 更换显示样式*/ 
function setTab(name,cursel,n){ 
for(i=1;i<=n;i++){ 
var menu=document.getElementById(name+i); 
var con=document.getElementById("con_"+name+"_"+i); 
menu.className=i==cursel?"hover":""; 
con.style.display=i==cursel?"block":"none"; 
} 
} 
//--> 
</script>




<?php
require("outerDB.php");
require("dbcenter.php");
error_reporting(E_ERROR);


$file = 'invest.ini';
$ini_array = array(
  'JOBS' => array (
      'db' =>  'jobs',      
      'sql' => array(        
          'ALL' => 'select * from jobs '
	  )
	)
);



echo '<div id="Tab1"> 
<div class="Menubox"> 
<ul> ';

$type = $_POST['type'];
$count = count($ini_array);
$start = 1;
foreach($ini_array as $k=>$v){
    if((!$type and $start==1) or $type == $k){
        echo "<li id='one$start' onClick=\"setTab('one',$start,$count)\" class='hover'>$k</li>";
    }else{
        echo "<li id='one$start' onClick=\"setTab('one',$start,$count)\" >$k</li>";        
    }
    $start++;
}

echo '</ul> 
</div> 
<div class="Contentbox"> ';
$start=1;
foreach($ini_array as $k=>$v){
    $str = "<table><tr><td><form method=POST><h3>$k</h3>";
    if(isset($v['input'])){
       $ra = explode(',',$v['input']);
       foreach($ra as $one){        
            $value  = $_POST[$one];
           $str.= "$one:<input type=text name='$one' value='$value'><br>";
       }
    }
    $str.= "<input type=hidden name=type value='$k'><input type=submit>";
    $str.= "</form></td></tr></table>";



    if((!$type and $start==1) or $type == $k){
        echo "<div id='con_one_$start' class='hover'>$str</div>";
    }else{
        echo "<div id='con_one_$start' style='display:none'>$str</div>";
    }
    $start++;
}


echo '
</div> 
</div>';


if(isset($_POST['type'])) {
    $type = $_POST['type'];
    $config = $ini_array[$type];
	$tUser = get_db($config['db']);   
    
	foreach($config[sql] as $one=>$li) {
		$select = $one['select'] ? $one['select']: "*";
        
        $ra = explode(',',$config['input']);
        $params= array();
        foreach($ra as $one2){
           $params[] = $_POST[$one2];
        }
        
		$users = $tUser->get_list_h(vsprintf($li, $params),true);
		print_table($one, $users,vsprintf($li, $params));
	}
}