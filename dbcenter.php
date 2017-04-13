<?php
define('WAITING',0);
define('EXECUTING',1);
define('FINISHED',2);
define('UNKNOW',3);
define('ERROR',99);


function get_db_config($type_name){
	$db_config = array(
        "default" => array("localhost","wuser", "sichang0923", "gen", 'utf8'),
        "ionic" => array("localhost","wuser", "sichang0923", "ionic", 'utf8'),
        "rico" => array("rds66onarg7ldygb9g77.mysql.rds.aliyuncs.com","ruser", "sichang0923", "gen", 'utf8'),
        "test" => array("localhost","root", "", "gen", 'utf8'),
        "prod" => array("localhost","root", "", "gen", 'utf8'),
        "genshop" => array("rds66onarg7ldygb9g77.mysql.rds.aliyuncs.com","ruser", "sichang0923", "mbyemaijiu273", 'utf8'),
        "session" => array("localhost","wuser", "sichang0923", "z_session", 'utf8'),
		//"jobs" => array("192.168.0.105","daniel", "123456", "jobs", 'utf8'),
		"jobs" => array("192.168.1.199","daniel", "123456", "jobs", 'utf8'),
		"hive" => array("192.168.1.199","ruser", "123456", "hive", 'utf8'),
		"jingrong" => array("192.168.1.199","daniel", "123456", "jingrong", 'utf8'),
    );
	return $db_config[$type_name];
}

function get_db($type_name){
    $db = get_db_config($type_name);
    $db_host = $db[0];
    $db_user = $db[1];
    $db_pass = $db[2];
    $db_name = $db[3];
    
    return new MysqlDAO($db_host, $db_user, $db_pass, $db_name);     
}


function show_lines_hc($datar,$up="",$down="",$left="",$width=600,$height=240){
	/*
	x y v
	-----
	A X 1
	A Y 2
	B X 3
	B Y 4
	     2  4  Y
	     1  3  X
	     A  B
	*/
	$catetory =array();
	foreach($datar as $one){        
        $catetory[] = "$one[x]";
    }
    $catetory = array_unique($catetory);
    $strcatetory = "'".implode("','",$catetory)."'";    
    $data = array();
    foreach($datar as $one){
    	if(!isset($data[$one[y]])){
    		$data[$one[y]]=array();
    	}
    	$index = $indexcategory[$one[x]];
    	$data[$one[y]][$one[x]] = $one[v];
    } 
    
    $divid = "".time()."_".rand(1,1000);
    $str = '<div id="'.$divid.'" style="margin-top:5px; margin-left:0px; width:'.$width.'px; height:'.$height.'px;"></div>';
    $str.="<script type='text/javascript'>
$(function () {
    $('#$divid').highcharts({
        title: {
            text: '$up',
            x: -20 //center
        },
        subtitle: {
            text: '$down',
            x: -20
        },
        xAxis: {
            categories: [$strcatetory]
        },
        yAxis: {
            title: {
                text: '$left'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: ''
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        credits: { enabled:false },
        series: [";
        ?>
<?php
		ksort($data);
		foreach($data as $k=>$v){
			$str.= "     {
            name: '$k',
            data: [";
            foreach($catetory as $kk) {
            	$str.=($v[$kk]?$v[$kk]:'0').',';
            }
            $str.="]}, ";
		}
        $str.= "
        ]
    });
});
		</script>";
return $str;

}


function print_table($title,$data,$sql){    
    $gen_keys = array_keys($data[0]);
    //sort($gen_keys,SORT_STRING);
    echo "\n<h2 align=center>$title($sql)</h2>";
    
    if(count($data)==0){
        echo "No Data!";return;
    }

    $col = 'grey';
    echo "<table border=1>";
    print '<tr>';
    foreach($gen_keys as $k1){
        echo "<th>",$k1," </th>";
    }
    echo "</tr>\n";
    foreach($data as $k){
        $col == 'grey' ? $col = 'lighblue' : $col = 'grey';
        print '<tr style="background-color:#'.$col.';">';
        foreach($gen_keys as $k1){
            echo "<td>",$k[$k1]," </td>";
        }
        echo "</tr>\n";
    }
    echo "</table>";
}

function getEng($name){
    $value = "";
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } elseif (isset($_GET[$name])) {
        $value = $_GET[$name];
    }else{
        return "";
    }
    return preg_match('/^[0-9A-Za-z_]+$/',$value)?$value:"";
}
function getParam($name,$default=''){
    $value = "";
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } elseif (isset($_GET[$name])) {
        $value = $_GET[$name];
    }
    return $value?$value:$default;
}


function show_pie($data){
     $divid = "".time()."_".rand(1,1000);
    $str = '<div id="'.$divid.'" style="margin-top:5px; margin-left:0px; width:420px; height:300px;"></div>';
    $str.="<script>

   $(document).ready(function(){
var data = [";
    
    foreach($data as $one){
        $str.= "['$one[label]',$one[num]],\n";
    }
    $str.="
];
var plot1 = jQuery.jqplot ('$divid', [data],
{
  seriesDefaults: {
    // Make this a pie chart.
    renderer: jQuery.jqplot.PieRenderer,
    rendererOptions: {
      // Put data labels on the pie slices.
      // By default, labels show the percentage of the slice.
      showDataLabels: true
    }
  },
  legend: { show:true, location: 'e' }
}
);
});</script>";
return $str;

}


function show_plots($data,$up="",$down="",$left="",$num=0){
    $divid = "".time()."_".rand(1,1000);
    $str = '<div id="'.$divid.'" style="margin-top:5px; margin-left:0px; width:600px; height:240px;"></div>';
    $str.="<script>

$(document).ready(function(){
var plot2 = $.jqplot ('$divid', [";
    
    $str.= "[";
    foreach($data as $one){
        $str.= "$one[num],";
    }
    $str.= "0],";

    for($i=1;$i<=$num;$i++){
    $str.= "[";
    foreach($data as $one){
        $str.= $one["num$i"].",";
    }
    $str.= "0],";
    }

   $str.= "], {
  // Give the plot a title.
  title: '$up',
  // You can specify options for all axes on the plot at once with
  // the axesDefaults object.  Here, we're using a canvas renderer
  // to draw the axis label which allows rotated text.
  axesDefaults: {
    labelRenderer: $.jqplot.CanvasAxisLabelRenderer
  },
  // Likewise, seriesDefaults specifies default options for all
  // series in a plot.  Options specified in seriesDefaults or
  // axesDefaults can be overridden by individual series or
  // axes options.
  // Here we turn on smoothing for the line.
  seriesDefaults: {
      rendererOptions: {
          smooth: true
      }
  },
  // An axes object holds options for all axes.
  // Allowable axes are xaxis, x2axis, yaxis, y2axis, y3axis, ...
  // Up to 9 y axes are supported.
  axes: {
    // options for each axis are specified in seperate option objects.
    xaxis: {
      label: '$down',
      // Turn off padding.  This will allow data point to lie on the
      // edges of the grid.  Default padding is 1.2 and will keep all
      // points inside the bounds of the grid.
      pad: 0
    },
    yaxis: {
      label: '$left'
    }
  }
});
});
</script>";
return $str;

}
function show_lines($data,$up="",$down="",$left="",$interval='7 days',$format='%m-%d',$width=600,$height=240){
    $divid = "".time()."_".rand(1,1000);
    $str = '<div id="'.$divid.'" style="margin-top:5px; margin-left:0px; width:'.$width.'px; height:'.$height.'px;"></div>
    <div id="img_'.$divid.'"></div>';
    $str.='<script>

    $(document).ready(function(){
    var data1=[';
    foreach($data as $one){
        $str.= "['$one[label]',$one[num]],\n";
    }
  $str.="];
    var plot1 = $.jqplot('$divid', [data1], {

        title:{
             text:'$up',
             show:true,
             fontSize:'10px',
             textColor:'#666',
        },
        seriesDefaults: {
              rendererOptions: {
                  smooth: true
              }
        },
        axes:{
            xaxis:{
                renderer:$.jqplot.DateAxisRenderer,
                tickOptions:{formatString:'$format'},
                //min:'2014-02-03',
                tickInterval:'$interval',
                label: '$down',
                
            },
            yaxis: {
              label: '$left'
            }
        },
        highlighter: {
                show: true, 
                showLabel: true, 
                tooltipAxes: '',
                sizeAdjust: 7.5 , tooltipLocation : 'ne'
        },
        cursor:{
                show: true, 
                zoom: true
        },
        series:[{lineWidth:2, markerOptions:{style:'filledSquare'}}]
    });
    //output image
    //var imgData = $('#$divid').jqplotToImageStr({});
    //var imgElem = $('<img/>').attr('src',imgData);
    //$('#img_$divid').append(imgElem);
});


</script>";
return $str;

}



function show_bars($data,$up="",$down="",$left="",$interval='7 days',$format='%m-%d',$width=600,$height=240){
    $divid = "".time()."_".rand(1,1000);
    $str = '<div id="'.$divid.'" style="margin-top:5px; margin-left:0px; width:'.$width.'px; height:'.$height.'px;"></div>
    <div id="img_'.$divid.'"></div>';
    $str.='<script>

    $(document).ready(function(){
    var s1=[';
    foreach($data as $one){
        $str.= "$one[num],";
    }
  $str.="];
  
  var ticks=[";
    foreach($data as $one){
        //$str.= "'".substr($one[label],0,8).'..'.substr($one[label],strlen($one[label])-8,8)."',\n";
        $str.= "'$one[label]',\n";
    }
  $str.="];
    $.jqplot.config.enablePlugins = true;

    plot1 = $.jqplot('$divid', [s1], {
            // Only animate if we're not using excanvas (not in IE 7 or IE 8)..
            animate: !$.jqplot.use_excanvas,
            title:{
                 text:'$up',
                 show:true,
                 fontSize:'10px',
                 textColor:'#666',
            },
            seriesDefaults:{
                renderer:$.jqplot.BarRenderer,
                pointLabels: { show: true }
            },
            axes: {
                xaxis: {
                    renderer: $.jqplot.CategoryAxisRenderer,
                    ticks: ticks
                },
                yaxis: {
                  label: '$left'
                }
            },
            highlighter: { show: false }
    });

    //output image
    //var imgData = $('#$divid').jqplotToImageStr({});
    //var imgElem = $('<img/>').attr('src',imgData);
    //$('#img_$divid').append(imgElem);
});


</script>";
return $str;

}
