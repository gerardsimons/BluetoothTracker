<?php
require_once("settings.php");

if ($_SESSION["loggedin"] != true)
{
	if (isset($_POST["pass"]))
	{
		if ($_POST["pass"] == $adminpass) $_SESSION["loggedin"] = true;
	}
	if ($_SESSION["loggedin"] != true)
	{ ?>
<div style="text-align:center">
    <form action="." method="post">
    	<input type="password" name="pass" /> <input type="submit" value="Login" />
    </form>
</div>
    <?php
		exit();
	}
}
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

var uniteditmode = <?php echo (isset($_GET["uniteditmode"])) ? "true": "false"; ?>;
var units = {<?php
$units = array();
$res = getRows("SELECT * FROM YesDemo_Units", array());
foreach ($res as $row) $units[] = $row["ID"].": [".$row["CoordX"].", ".$row["CoordY"]."]";
echo implode(", ", $units);
?>};

var labels = {};

$(document).ready(function(e) {
    imgw = $("#mapimg").width();
	imgh = $("#mapimg").height();
	$("#mapimg").css({width: "100%", height: "100%"});
	$("#map").draggable({
		drag: function(e, ui) {
			var left = ui.position.left
			var top = ui.position.top;
			
			var fullw = $("#bg").width();
			var fullh = $("#bg").height();
			
			var mapw = $("#map").width();
			var maph = $("#map").height();
			
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
	setTimeout(SetMapSize(), 500);
	DrawUnits();
	DrawLabels();
});

function SetMapSize() {
	var fullw = $("#bg").width();
	var fullh = $("#bg").height();
	
	var mapw = fullw * (zoom / 100);
	var maph = mapw * (imgh / imgw);
	
	$("#map").width(mapw);
	$("#map").height(maph);
	
	var centerxcoord = mapw * (centerx / 100);
	var centerycoord = maph * (centery / 100);
	var mapleft = fullw * 0.5 - centerxcoord;
	var maptop = fullh * 0.5 - centerycoord;
	
	$("#map").css({left: mapleft+"px", top: maptop+"px"});
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
	if (uniteditmode == true) {
		$(".whereatunit").draggable({
			containment: "parent",
			stop: function(e, ui) {
				var left = ui.position.left
				var top = ui.position.top;
				
				var mapw = $("#map").width();
				var maph = $("#map").height();
				
				var coordx = (left / mapw) * 100;
				var coordy = ((maph - top) / mapw) * 100;
				
				var id = $(this).attr("id").substr(4);
				
				ReportPos(id, coordx, coordy);
			}
		});
	}
}

function ReportPos(unitid, coordx, coordy) {
	$.ajax("action.php?action=setcoordunit&id="+unitid+"&coordx="+coordx+"&coordy="+coordy);
}

function DrawLabels() {
	$(".whereatlabel").remove();
	for (var id in labels) {
		var coordx = labels[id][0];
		var coordy = labels[id][1];
		coordy = coordy * (imgw / imgh);
		var left = coordx;
		var top = 100 - coordy;
		$("#map").append("<div style='left:"+left+"%;top:"+top+"%;' class='itemwrapper whereatlabel'><div class='labeldot'></div>"+id+"</div>");
	}
}
</script>
