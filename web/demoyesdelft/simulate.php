<?php
require_once("settings.php");
?>

<style type="text/css">
body, html {
	margin:0;
	padding:0;
}

#bg {
	width:100%;
	height:100%;
	overflow:hidden;
	position:relative;
}

#map {
	position:absolute;
}

.itemwrapper {
	position:absolute;
}
.itemtext {
	z-index:150;
}

.unitdot {
	position:relative;
	left:-4px;
	top:-4px;
	height:8px;
	width:8px;
	background:#f00;
	border-radius:4px;
}

.labeldot {
	position:relative;
	left:-6px;
	top:-6px;
	height:12px;
	width:12px;
	background:#0f0;
	border-radius:6px;
	z-index:100;
}
.accuracywrapper {
	position:absolute;
	z-index:50;
}
.accuracy {
	opacity:0.2;
	background:#0CF;
	border:1px #00F solid;
	width:100%;
	height:100%;
	border-radius:50%;
	z-index:50;
	box-sizing:border-box;
}
</style>

<div id="bg">
	<div id="map">
    	<img src="map.png" id="mapimg" />
    </div>
</div>

<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script src="jquery.mousewheel.js" type="text/javascript"></script>
<script type="text/javascript">
var zoom = 100;
var imgw = 0;
var imgh = 0;
var centerx = 50;
var centery = 50;
var mapw = 0;
var maph = 0;

var units = {<?php
$xs = array();
$ys = array();
$units = array();
$labeldisdata = array();
$res = getRows("SELECT * FROM YesDemo_Units", array());
foreach ($res as $row)
{
	$units[] = $row["ID"].": [".$row["CoordX"].", ".$row["CoordY"]."]";
	$labeldisdata[] = $row["ID"].": {1: [0, 0]}";
	$xs[] = $row["CoordX"];
	$ys[] = $row["CoordY"];
}
echo implode(", ", $units);
?>};

var labels = {1: [0, 0, 0]};
var fromts = 0;

var labeldisdata = {<?php echo implode(", ", $labeldisdata); ?>};

var th = 0;
function AnimateLabels() {
	var xstart = <?php echo array_sum($xs) / count($xs); ?>;
	var ystart = <?php echo array_sum($ys) / count($ys); ?>;
	var r = 2;
	
	var x = xstart + r * Math.cos(th);
	var y = ystart + r * Math.sin(th);
	
	labels[1][0] = x;
	labels[1][1] = y;
	
	th += Math.PI / 180;
	
	DrawLabels();
	
	CalcDistance();
	
	setTimeout(AnimateLabels, 100);
}

function CalcDistance() {
	var noisewidth = 0.0;
	for (var i in labeldisdata) {
		var xunit = units[i][0];
		var yunit = units[i][1];
		for (var a in labeldisdata[i]) {
			var xlabel = labels[a][0];
			var ylabel = labels[a][1];
			
			var d = Math.sqrt(Math.pow(xunit - xlabel, 2) + Math.pow(yunit - ylabel, 2));
			
			d = d * ((1 - noisewidth) + Math.random() * (2 * noisewidth));
			
			labeldisdata[i][a][0] = d;
			
			var frac = 2 / d;
			var ddB = 10 * (Math.log(frac) / Math.LN10);
			labeldisdata[i][a][1] = ddB - 30;
		}
	}
	//console.log(labeldisdata[1][1][1], labeldisdata[2][1][1], labeldisdata[3][1][1], labeldisdata[4][1][1]);
}

var tid = 0;
function ReportSignal() {
	var url = "reportsignal.php?apikey=jrZ5H2mdbf8LXB41Uj47ccad&unitid=";
	
	for (var i in labeldisdata) {
		var uurl = url + i;
		for (var a in labeldisdata[i]) {
			var signal = labeldisdata[i][a][1];
			uurl += "&labelids[]="+a+"&signals[]="+signal;
		}
		$.ajax(uurl, {
			success: function() {
				clearTimeout(tid);
				tid = setTimeout(ReportSignal, 1000);
			}
		});
	}
}

$(document).ready(function(e) {
	$("#map").draggable({
		drag: function(e, ui) {
			var left = ui.position.left
			var top = ui.position.top;
			
			var fullw = $("#bg").width();
			var fullh = $("#bg").height();
			
			var centerxcoord = fullw * 0.5 - left;
			var centerycoord = fullh * 0.5 - top;
			
			centerx = (centerxcoord / mapw) * 100;
			centery = (centerycoord / maph) * 100;
		}
	});
	SetMapSize();
	$("#mapimg").load(function(e) {
        SetMapSize();
    });
	$("#map").mousewheel(function(e) {
		zoom = zoom * (1 + .1 * e.deltaY);
		SetMapSize();
	});
	setTimeout(SetMapSize, 500);
	DrawUnits();
	AnimateLabels();
	setTimeout(ReportSignal, 500);
});

function SetMapSize() {
	if (imgh == 0) {
		imgh = $("#mapimg").height();
		if (imgh > 0) {
			imgw = $("#mapimg").width();
			$("#mapimg").css({width: "100%", height: "100%"});
		}
		else
			return;
	}
	
	var fullw = $("#bg").width();
	var fullh = $("#bg").height();
	
	mapw = fullw * (zoom / 100);
	maph = mapw * (imgh / imgw);
	
	$("#map").width(mapw);
	$("#map").height(maph);
	
	var centerxcoord = mapw * (centerx / 100);
	var centerycoord = maph * (centery / 100);
	var mapleft = fullw * 0.5 - centerxcoord;
	var maptop = fullh * 0.5 - centerycoord;
	
	$("#map").css({left: mapleft+"px", top: maptop+"px"});
	
	DrawLabels();
}

function DrawUnits() {
	for (var id in units) {
		var coordx = units[id][0];
		var coordy = units[id][1];
		coordy = coordy * (imgw / imgh);
		var left = coordx;
		var top = 100 - coordy;
		$("#map").append("<div style='left:"+left+"%;top:"+top+"%;' class='itemwrapper whereatunit' id='unit"+id+"'><div class='unitdot'></div>"+id+"</div>");
	}
}

function DrawLabels() {
	$(".whereatlabel").remove();
	for (var id in labels) {
		var coordx = labels[id][0];
		var coordy = labels[id][1];
		var accuracy = labels[id][2];
		
		coordy = coordy * (imgw / imgh);
		var left = coordx;
		var top = 100 - coordy;
		
		var accw = mapw * (accuracy / 100);
		var accl = - accw * 0.5;
		
		$("#map").append("<div style='left:"+left+"%;top:"+top+"%;' class='itemwrapper whereatlabel'><div class='accuracywrapper' style='left:"+accl+"px;top:"+accl+"px;width:"+accw+"px;height:"+accw+"px'><div class='accuracy'></div></div><div class='labeldot'></div><div class='itemtext'>"+id+"</div></div>");
	}
}
</script>