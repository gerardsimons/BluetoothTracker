<?php
require_once("settings.php");

//Google Maps API key for free Google Maps Javascript API, registered by jbussemaker.com@gmail.com (Jasper Bussemaker)
?>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyARywG_CYNiZ2gUB_Ks4zlpuMYtQe5wQO0"></script>
<script type="text/javascript" src="infobox.js"></script>

<style type="text/css">
body, html {
	margin:0;
	padding:0;
	font-family:Arial, Helvetica, sans-serif;
}

#bg {
	width:100%;
	height:100%;
	overflow:hidden;
	position:relative;
}

#map {
	position:absolute;
	width:100%;
	height:100%;
	left:0px;
	top:0px;
}

.itemwrapper {
	position:absolute;
}
.itemtext {
	z-index:150;
}

.unitdot {
	position:relative;
	left:-6px;
	top:-6px;
	height:12px;
	width:12px;
	background:#27348b;
	border-radius:5px;
	border:1px solid #fff;
	box-sizing:border-box;
}
.unittext {
	position:relative;
	top:-30px;
}

.labeldot {
	position:relative;
	left:-4px;
	top:-4px;
	height:8px;
	width:8px;
	background:#0f0;
	border-radius:4px;
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

.controlcontainer {
	position:fixed;
	right:10px;
	top:10px;
	/*border:1px #999 solid;*/
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
}
button {
	padding:10px 15px;
	font-size:larger;
	margin:0;
	outline:none;
	border:1px #999 solid;
	border-bottom:none;
	/*-webkit-box-shadow: 0px 0px 3px 0px rgba(50, 50, 50, 0.25);
	-moz-box-shadow:    0px 0px 3px 0px rgba(50, 50, 50, 0.25);
	box-shadow:         0px 0px 3px 0px rgba(50, 50, 50, 0.25);*/
	background: #f7f7f7; /* Old browsers */
	background: -moz-linear-gradient(top,  #f7f7f7 0%, #c9c9c9 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f7f7f7), color-stop(100%,#c9c9c9)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #f7f7f7 0%,#c9c9c9 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #f7f7f7 0%,#c9c9c9 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #f7f7f7 0%,#c9c9c9 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #f7f7f7 0%,#c9c9c9 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f7f7f7', endColorstr='#c9c9c9',GradientType=0 ); /* IE6-9 */
	cursor:pointer;
	text-align:center;
	width:200px;
}
button.topradius {
	-webkit-border-top-left-radius: 5px;
	-webkit-border-top-right-radius: 5px;
	-moz-border-radius-topleft: 5px;
	-moz-border-radius-topright: 5px;
	border-top-left-radius: 5px;
	border-top-right-radius: 5px;
}
button.bottomradius {
	-webkit-border-bottom-right-radius: 5px;
	-webkit-border-bottom-left-radius: 5px;
	-moz-border-radius-bottomright: 5px;
	-moz-border-radius-bottomleft: 5px;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	border-bottom:1px #999 solid;
}
button.on {
	background: #c9c9c9; /* Old browsers */
	background: -moz-linear-gradient(top,  #c9c9c9 0%, #f7f7f7 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#c9c9c9), color-stop(100%,#f7f7f7)); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  #c9c9c9 0%,#f7f7f7 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  #c9c9c9 0%,#f7f7f7 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  #c9c9c9 0%,#f7f7f7 100%); /* IE10+ */
	background: linear-gradient(to bottom,  #c9c9c9 0%,#f7f7f7 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#c9c9c9', endColorstr='#f7f7f7',GradientType=0 ); /* IE6-9 */
}
</style>

<div id="bg">
	<div id="map"></div>
    <?php if ($_SESSION["loggedin"] == true) { ?>
        <div class="controlcontainer">
            <button class="topradius on" id="mapbutton">Map</button><br />
            <button id="sattelitebutton">Sattelite</button><br />
            <button class="on" id="yesdelftbutton">Yes!Delft</button><br />
            <button class="on" id="boxesbutton">Access Points</button><br />
            <button class="on bottomradius" id="tagsbutton">Tags</button>
        </div>
    <?php } ?>
</div>
<?php
if ($_SESSION["loggedin"] != true)
{
	if (isset($_POST["pass"]) && isset($_POST["username"]))
	{
		if ($_POST["pass"] == $adminpass && $_POST["username"] == $adminuser)
		{
			$_SESSION["loggedin"] = true;
			header("Location: ./");
			exit();
		}
	}
	if ($_SESSION["loggedin"] != true)
	{ ?>
<div style="position:fixed;left:50%;top:0px">
	<div style="position:absolute;left:0px;top:100px;">
        <div style="text-align:center;position:relative;left:-50%;padding:50px;background:#fff;">
            <form action="." method="post">
        		<div style="text-align:left">
                	Username:<br />
                    <input type="text" name="username" /><br /><br />
                    
                    Password:<br />
	                <input type="password" name="pass" />
                </div><br />
                <input type="submit" value="Login" />
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(e) {
	var opts = {
		center: new google.maps.LatLng(51.993102, 4.386495), //whereAt Yes!Delft coordinates
		zoom: 16,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		disableDefaultUI: true
	};
	var map = new google.maps.Map(document.getElementById("map"), opts);
});
</script>
    <?php
		exit();
	}
}
?>

<script type="text/javascript">
var drawunits = true;
var drawlabels = true;

//units
var startcu = [2, 3, 9]; //27348b -> 239
var endcu = [10, 10, 10]; //aaa
var cfromtsu = 60 * 10; //[s], number of seconds after which the color starts to fade from startc to endc
var ctotsu = 3600; //[s], number of seconds after which the color is at endc
//labels
var startc = [0, 15, 0]; //0f0
var endc = [10, 10, 10]; //aaa
var cfromts = 60 * 10; //[s], number of seconds after which the color starts to fade from startc to endc
var ctots = 3600; //[s], number of seconds after which the color is at endc

var uniteditmode = <?php echo (isset($_GET["uniteditmode"])) ? "true": "false"; ?>;
var units = {<?php
$minlat = false;
$maxlat = false;
$minlon = false;
$maxlon = false;

$units = array();
$res = getRows("SELECT * FROM YesDemo_Units WHERE Hide<>1 OR Hide IS NULL", array());
foreach ($res as $row)
{
	$lat = $row["Lat"];
	$lon = $row["Lon"];
	$name = $row["Name"];
	if ($name === NULL || $name == "") $name = $row["ID"];
	if ($lat < $minlat || $minlat === false) $minlat = $lat;
	if ($lat > $maxlat || $maxlat === false) $maxlat = $lat;
	if ($lon < $minlon || $minlon === false) $minlon = $lon;
	if ($lon > $maxlon || $maxlon === false) $maxlon = $lon;
	$units[] = $row["ID"].": [".$row["Lat"].", ".$row["Lon"].", 0, '".htmlspecialchars($name)."']";
}
$lat = (count($lats) > 0) ? array_sum($lats) / count($lats): 0;
$lon = (count($lons) > 0) ? array_sum($lons) / count($lons): 0;
echo implode(", ", $units);
?>};

var labels = {<?php
$res = $api->call("label", "getlabels", array());
$labelnames = array();
if (!isset($res["error"]))
{
	foreach ($res as $row)
	{
		$id = $row["id"];
		$name = $row["name"];
		$labelnames[$id] = $name;
	}
}

$labels = array();
$res = getRows("SELECT p.* FROM (SELECT * FROM YesDemo_Positions ORDER BY Timestamp DESC) AS p GROUP BY p.LabelID", array());
foreach ($res as $row)
{
	$lat = $row["Lat"];
	$lon = $row["Lon"];
	if ($lat < $minlat || $minlat === false) $minlat = $lat;
	if ($lat > $maxlat || $maxlat === false) $maxlat = $lat;
	if ($lon < $minlon || $minlon === false) $minlon = $lon;
	if ($lon > $maxlon || $maxlon === false) $maxlon = $lon;
	$id = $row["LabelID"];
	$ago = microtime(true) - $row["Timestamp"];
	$name = (isset($labelnames[$id])) ? $labelnames[$id]: $id;
	$labels[] = "$id: [".$row["Lat"].", ".$row["Lon"].", ".$row["Acc"].", $ago, '".$name."']";
}
echo implode(", ", $labels);
?>};

var map = null;
var overlay = null;

$(document).ready(function(e) {
	var opts = {
		center: new google.maps.LatLng(51.993102, 4.386495), //whereAt Yes!Delft coordinates
		zoom: 16,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		streetViewControl: false,
		mapTypeControl: false
	};
	map = new google.maps.Map(document.getElementById("map"), opts);
	
	<?php if ($minlat !== false) { ?>
		map.fitBounds(new google.maps.LatLngBounds(new google.maps.LatLng(<?php echo $minlat; ?>, <?php echo $minlon; ?>), new google.maps.LatLng(<?php echo $maxlat; ?>, <?php echo $maxlon; ?>)));
	<?php } ?>
	
	google.maps.event.addListener(map, "click", function(e) {console.log(e.latLng.lat(), e.latLng.lng())});
	
	//Yes!Delft overlay
	var opts = {
		map: map,
		clickable: false,
		opacity: 1
	};
	var bounds = new google.maps.LatLngBounds(new google.maps.LatLng(51.99279772767634, 4.385765983829455), new google.maps.LatLng(51.99391175651188, 4.387879582114027));
	overlay = new google.maps.GroundOverlay("mapnorth.png", bounds, opts);
	//return;
	
	$("#mapbutton").click(function(e) {
        map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
		$("#mapbutton").addClass("on");
		$("#sattelitebutton").removeClass("on");
    });
	$("#sattelitebutton").click(function(e) {
        map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
		$("#mapbutton").removeClass("on");
		$("#sattelitebutton").addClass("on");
    });
	$("#yesdelftbutton").click(function(e) {
        if ($(this).hasClass("on")) {
			overlay.setMap(null);
			$(this).removeClass("on");
		} else {
			overlay.setMap(map);
			$(this).addClass("on");
		}
    });
	$("#boxesbutton").click(function(e) {
        if ($(this).hasClass("on")) {
			drawunits = false;
			DrawUnits();
			$(this).removeClass("on");
		} else {
			drawunits = true;
			DrawUnits();
			$(this).addClass("on");
		}
    });
	$("#tagsbutton").click(function(e) {
        if ($(this).hasClass("on")) {
			drawlabels = false;
			DrawLabels();
			$(this).removeClass("on");
		} else {
			drawlabels = true;
			DrawLabels();
			$(this).addClass("on");
		}
    });
	
	DrawUnits();
	DrawLabels();
	UpdateLabels();
});

var unitmarkers = [];
var prevunitstate = {};

function DrawUnits() {
	if (uniteditmode == false) {
		if (drawunits == false) {
			for (var id in units) {
				if (typeof unitmarkers[id] != "undefined") {
					unitmarkers[id].close();
				}
				if (typeof prevunitstate[id] != "undefined") {
					delete prevunitstate[id];
				}
			}
			return;
		}
		for (var id in units) {
			var lat = units[id][0];
			var lon = units[id][1];
			var ago = units[id][2];
			var name = units[id][3];
			
			if (ago < cfromtsu) {
				var rgb = startcu;
			} else if (ago > ctotsu) {
				var rgb = endcu;
			} else {
				var frac = (ago - cfromtsu) / (ctotsu - cfromtsu);
				var rgb = [];
				for (var i=0;i<3;i++) {
					rgb[i] = Math.round(startcu[i] + (endcu[i] - startcu[i]) * frac);
				}
			}
			var color = "#";
			for (var i=0;i<3;i++) {
				color += rgb[i].toString(16);
			}
			
			if (typeof prevunitstate[id] != "undefined") {
				if (lat == prevunitstate[id][0] && lon == prevunitstate[id][1] && color == prevunitstate[id][2]) continue;
			}
			
			var opts = {
				content: "<div class='itemwrapper' id='unit"+id+"'><div class='unitdot' style='background:"+color+"'></div><div class='unittext'>"+name+"</div></div>",
				disableAutoPan: true,
				pixelOffset: new google.maps.Size(0,0),
				position: new google.maps.LatLng(lat, lon),
				closeBoxURL: "",
				isHidden: false,
				pane: "floatPane",
				enableEventPropagation: true,
				zIndex: 9999,
				draggable: true
			};
			if (typeof unitmarkers[id] != "undefined") {
				unitmarkers[id].close();
			}
			unitmarkers[id] = new InfoBox(opts);
			unitmarkers[id].open(map);
			
			prevunitstate[id] = [lat, lon, color];
		}
	} else {
		for (var id in units) {
			var lat = units[id][0];
			var lon = units[id][1];
			var opts = {
				map: map,
				draggable: true,
				position: new google.maps.LatLng(lat, lon),
				title: id
			}
			unitmarkers[id] = new google.maps.Marker(opts);
			google.maps.event.addListener(unitmarkers[id], "dragend", function(e) {
				var lat = e.latLng.lat();
				var lon = e.latLng.lng();
				ReportPos(this.title, lat, lon);
			});
		}
	}
}

var labelmarkers = [];
var labelcircles = [];
var prevstate = {};

function DrawLabels() {
	if (drawlabels == false) {
		for (var id in labels) {
			if (typeof labelmarkers[id] != "undefined") {
				labelmarkers[id].close();
			}
			if (typeof labelcircles[id] != "undefined") {
				labelcircles[id].setMap(null);
				delete labelcircles[id];
			}
			if (typeof prevstate[id] != "undefined") {
				delete prevstate[id];
			}
		}
		return;
	}
	for (var id in labels) {
		var lat = labels[id][0];
		var lon = labels[id][1];
		var acc = labels[id][2];
		var ago = labels[id][3];
		var name = labels[id][4];
		
		if (ago < cfromts) {
			var rgb = startc;
		} else if (ago > ctots) {
			var rgb = endc;
		} else {
			var frac = (ago - cfromts) / (ctots - cfromts);
			var rgb = [];
			for (var i=0;i<3;i++) {
				rgb[i] = Math.round(startc[i] + (endc[i] - startc[i]) * frac);
			}
		}
		var color = "#";
		for (var i=0;i<3;i++) {
			color += rgb[i].toString(16);
		}
		
		if (typeof prevstate[id] != "undefined") {
			if (lat == prevstate[id][0] && lon == prevstate[id][1] && acc == prevstate[id][2] && color == prevstate[id][3]) continue;
		}
		
		var opts = {
			content: "<div class='itemwrapper'><div class='labeldot' style='background:"+color+"'></div><div class='itemtext'>"+name+"</div></div>",
			disableAutoPan: true,
			pixelOffset: new google.maps.Size(0,0),
			position: new google.maps.LatLng(lat, lon),
			closeBoxURL: "",
			isHidden: false,
			pane: "floatPane",
			enableEventPropagation: true,
			zIndex: 9999,
			draggable: true
		};
		if (typeof labelmarkers[id] != "undefined") {
			labelmarkers[id].close();
		}
		labelmarkers[id] = new InfoBox(opts);
		labelmarkers[id].open(map);
		
		var opts = {
			map: map,
			center: new google.maps.LatLng(lat, lon),
			fillColor: color,
			fillOpacity: 0.2,
			radius: acc,
			strokeColor: color,
			strokeOpacity: 1,
			strokeWeight: 2,
			clickable: false
		};
		if (typeof labelcircles[id] != "undefined") {
			labelcircles[id].setMap(null);
			delete labelcircles[id];
		}
		labelcircles[id] = new google.maps.Circle(opts);
		
		prevstate[id] = [lat, lon, acc, color];
	}
}

function ReportPos(unitid, lat, lon) {
	$.ajax("action.php?action=setcoordunit&id="+unitid+"&lat="+lat+"&lon="+lon);
}

function UpdateLabels() {
	$.ajax({
		url: "updatelabels.php",
		cache: false,
		dataType: "script",
		success: function() {
			DrawLabels();
			if (uniteditmode == false) DrawUnits();
			setTimeout(UpdateLabels, 1000);
		}
	});
	$.ajax({url: "triangulate.php", cache: false});
}
</script>
