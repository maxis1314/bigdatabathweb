

 
 
 
 






</div>

<div class="container-fluid">
    <hr>

        <p>&nbsp;</p>

</div>


<script src="pubic/static/bootstrap/js/bootstrap.min.js"></script>
<script src="pubic/static/bootstrap/js/jquery-ui-1.10.0.custom.min.js"></script>


<script type="text/javascript" src="./MyAdmin_files/jquery.jqplot.min.js"></script>

<script class="include" type="text/javascript" src="jqplot/plugins/jqplot.barRenderer.min.js"></script>
<script class="include" type="text/javascript" src="jqplot/plugins/jqplot.pieRenderer.min.js"></script>
<script class="include" type="text/javascript" src="jqplot/plugins/jqplot.categoryAxisRenderer.min.js"></script>
<script class="include" type="text/javascript" src="jqplot/plugins/jqplot.pointLabels.min.js"></script>


<script type="text/javascript" src="./MyAdmin_files/jqplot.canvasTextRenderer.min.js"></script>
<script type="text/javascript" src="./MyAdmin_files/jqplot.canvasAxisLabelRenderer.min.js"></script>
<script type="text/javascript" src="./MyAdmin_files/jqplot.dateAxisRenderer.min.js"></script>
<script type="text/javascript" src="./MyAdmin_files/jqplot.highlighter.min.js"></script>
<script type="text/javascript" src="./MyAdmin_files/jqplot.cursor.min.js"></script>
<script type="text/javascript" src="./MyAdmin_files/jqplot.donutRenderer.min.js"></script>
<link href="./MyAdmin_files/jquery.jqplot.min.css" rel="stylesheet">


<div style="display:none;" class="back-to" id="toolBackTop">
<a title="返回顶部" onclick="window.scrollTo(0,0);return false;" href="#top" class="back-top">
返回顶部</a>
</div>

<style>

.back-to {bottom: 35px;overflow:hidden;position:fixed;right:10px;width:50px;z-index:999;}
.back-to .back-top {background: url("pub/images/back-top.png") no-repeat scroll 0 0 transparent;display: block;float: right;height:50px;margin-left: 10px;outline: 0 none;text-indent: -9999em;width: 50px;}
#.back-to .back-top:hover {background-position: -50px 0;}
</style>
<script type="text/javascript">
$(document).ready(function () {
        var bt = $('#toolBackTop');
        var sw = $(document.body)[0].clientWidth;

        var limitsw = (sw - 1200) / 2 - 40;
        if (limitsw > 0){
                limitsw = parseInt(limitsw);
                bt.css("right",limitsw);
        }

        $(window).scroll(function() {
                var st = $(window).scrollTop();
                if(st > 30){
                        bt.show();
                }else{
                        bt.hide();
                }
        });
})
</script>

 
  </body>
</html>
 