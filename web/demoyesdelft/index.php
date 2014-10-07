<?php
require_once("settings.php");

$loggedin = (isset($_SESSION["loggedin"])) ? $_SESSION["loggedin"]: false;

if ($loggedin == false)
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
}
if ($loggedin == true)
{
	if (isset($_GET["logout"]))
	{
		$_SESSION["loggedin"] = false;
		header("Location: ./");
		exit();
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="nl" lang="nl">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1" />
		<link rel="stylesheet" href="styles.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="app.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="responsive.css" type="text/css" media="screen" />
		<title>whereAt Cloud Demo</title>
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
		<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyARywG_CYNiZ2gUB_Ks4zlpuMYtQe5wQO0"></script>
        <script type="text/javascript" src="infobox.js"></script>
        <?php if ($loggedin == true) { ?>
			<script type="text/javascript">
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
            
			$labeldata = array();
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
				$labeldata[$id] = $name;
                $labels[] = "$id: [".$row["Lat"].", ".$row["Lon"].", ".$row["Acc"].", $ago, '".$name."', 1]";
            }
            echo implode(", ", $labels);
            ?>};
			
			var mapbounds = <?php if ($minlat !== false) { ?>new google.maps.LatLngBounds(new google.maps.LatLng(<?php echo $minlat; ?>, <?php echo $minlon; ?>), new google.maps.LatLng(<?php echo $maxlat; ?>, <?php echo $maxlon; ?>));<?php } else { ?>false;<?php } ?>
            </script>
            <script src="script.js" type="text/javascript"></script>
        <?php } else { ?>
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
        <?php } ?>
	</head>
<body<?php if ($loggedin == false) { ?> class="loggedout"<?php } ?>>
    <div id="header">
    	<img src="mobilemenu.png" id="mobilemenu" />
        <span style="font-size:large">Cluster Network</span>
		<?php if ($loggedin == true) { ?>
        	<div id="logoutcontainer">
            	<a href="./?logout"><img src="logout.png" /></a>
            </div>
    	<?php } else { ?>
        
        <?php } ?>
    </div>
	<?php if ($loggedin == true) { ?>
        <div id="controlpanel">
    		<div id="controlpanelshadow"></div>
            <h2>Tags:</h2><br />
            
            <?php
			if (count($labeldata) == 0)
				echo "No tags available.";
			else
			{
				asort($labeldata);
				foreach ($labeldata as $id=>$name)
				{
					echo "<label><input type='checkbox' labelid='$id' id='label_$id' class='labelcheckbox' checked='checked' /> $name | T°: n/a</label><br />";
				}
			}
			?>
            <br />
            
            <h2>Focus on:</h2><br />
            
            <button id="pos_you" class="block">Your position</button>
            <button id="pos_warehouse" class="block">Warehouse</button>
            <button id="pos_bullseye" class="block">Selected tags</button><br />
            
            <h2>Settings:</h2><br />
            
            <div id="settingscontainer">
                <span class="togglelabel">Map type:</span>
                <div class="togglecontainer">
                    <button class="toggle left active" name="maptype" value="map">Map</button><button class="toggle right" name="maptype" value="sat">Sat</button>
                </div><br /><br />
                
                <span class="togglelabel">Indoor:</span>
                <div class="togglecontainer">
                    <button class="toggle left active" name="indoor" value="on">On</button><button class="toggle right" name="indoor" value="off">Off</button>
                </div><br /><br />
                
                <span class="togglelabel">Access points:</span>
                <div class="togglecontainer">
                    <button class="toggle left active" name="access" value="on">On</button><button class="toggle right" name="access" value="off">Off</button>
                </div><br /><br />
                
                <span class="togglelabel">Info:</span>
                <div class="togglecontainer">
                    <button class="toggle left active" name="labelinfo" value="on">On</button><button class="toggle right" name="labelinfo" value="off">Off</button>
                </div><br /><br />
                
                <span class="togglelabel">Live:</span>
                <div class="togglecontainer">
                    <button class="toggle left active" name="labellive" value="rt">Realtime</button><button class="toggle right" name="labellive" value="on">On</button>
                </div>
            </div>
        </div>
    <?php } ?>
    <div id="container">
    	<div id="headershadow"></div>
    	<div id="mapwrapper">
        	<div id="map"></div>
        </div>
        <?php if ($loggedin == false) { ?>
            <div id="loginscreen">
                <form action="./?login" method="post">
                    <div style="text-align:left">
                        Username:<br />
                        <input type="text" name="username" /><br /><br />
                        
                        Password:<br />
                        <input type="password" name="pass" />
                    </div><br />
                    <input type="submit" value="Login" />
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>