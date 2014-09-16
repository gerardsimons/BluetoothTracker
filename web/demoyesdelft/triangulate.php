<?php
/* This script does two things:
** 1.	It finds sets of measurements of approximately the same time and groups them
** 2.	It takes these sets of measurement data and calculates the corresponding position
*/
require_once("settings.php");

header("Content-Type: text/plain");
error_reporting(E_ALL);
ini_set("display_errors", 1);

//triangulation settings
$updateinterval = 5; //sec
$resetupdating = 20; //sec
$minDiff = 60;
$f = 3; //parameter for n_units = 2
$sig = 4; //parameter for n_units = 2
$refacc = 1; //accuracy circle amplification
$tsrange = 20; //sec, used in selecting data
$labelrange = 35; //[m], for the accuracy bubble
$factor2 = 4; //amplification factor for accuracy of positioning with only 2 units

$lastupdate = GetSetting("lastupdate");
$nowupdating = GetSetting("nowupdating");
if (is_numeric($lastupdate))
{
	if ($lastupdate > time() - $updateinterval) exit();
}
SetSetting("lastupdate", microtime(true));
if ($nowupdating == 1)
{
	if (is_numeric($lastupdate))
	{
		if ($lastupdate < time() - $resetupdating) $nowupdating = 0;
	}
	exit();
}
SetSetting("nowupdating", 1);

// Get measurements from database
//$data = getRows("SELECT * FROM YesDemo_Tracking"); //overflows..
$data = getRows("SELECT m.LabelID, m.UnitID, AVG(t.SignalStrength) AS SignalStrength, m.MaxTime AS Timestamp FROM (SELECT UnitID, LabelID, Max(Timestamp) AS MaxTime, SignalStrength FROM YesDemo_Tracking GROUP BY UnitID, LabelID) AS m JOIN YesDemo_Tracking AS t ON t.UnitID=m.UnitID WHERE t.LabelID=m.LabelID AND t.Timestamp>=m.MaxTime-$tsrange GROUP BY UnitID, LabelID"); //hotseflots

// Group the data in measurement series per label and per unit
$measurements = array();
foreach ($data as $measurement)
{
	$labelID = $measurement["LabelID"];
	$unitID  = $measurement["UnitID"];
	if (!isset($measurements[$labelID])) $measurements[$labelID] = array();
	$measurements[$labelID][$unitID] = array
	(
		$measurement["UnitID"],
		$measurement["LabelID"],
		$measurement["Timestamp"],
		$measurement["SignalStrength"]
	);
}

/*$unitCoords = array();
$rows = getRows("SELECT t.UnitID, t.Lat, t.Lon, t.Acc FROM (SELECT UnitID, Max(Timestamp) AS MaxTime FROM YesDemo_Tracking GROUP BY UnitID) AS m JOIN YesDemo_Tracking AS t ON t.UnitID=m.UnitID WHERE t.Timestamp=m.MaxTime GROUP BY UnitID");
foreach ($rows as $row)
{
	$unitID  = $row["UnitID"];
	$lat = $row["Lat"];
	$lon = $row["Lon"];
	$acc = $row["Acc"];
	if ($lat !== NULL && $lon !== NULL && $acc !== NULL) $unitCoords[$unitID] = array($lat, $lon, $acc);
}*/

// Get associative array with UnitIDs as keys and their X and Y coordinates as values
$units = getRows("SELECT ID, Lat, Lon, Acc FROM YesDemo_Units");
foreach ($units as $unit)
{
	$unitID = $unit["ID"];
	$acc = ($unit["Acc"] === NULL) ? false: $unit["Acc"];
	if (!isset($unitCoords[$unitID])) $unitCoords[$unitID] = array($unit["Lat"], $unit["Lon"], $acc);
}

//$labelIDs = array_column(getRows("SELECT ID FROM Labels"), "ID");
// Populate the datasets
//print_r($unitCoords);
$dataSets = array();
foreach ($measurements as $labelID=>$labelSeries)
{
	var_dump($labelID);
	/*if (isset($measurements[$labelID]))
	{*/
		//$labelSeries = $measurements[$labelID];
		
		// Mark the first unit in the data as reference unit
		/*reset($labelSeries);
		$firstUnitSeries = current($labelSeries);*/
		
		//$labelSets = array();
		
		
		$set = array();
		$mints = false;
		$maxts = false;
		$minlat = false;
		$minlon = false;
		$maxlat = false;
		$maxlon = false;
		$lats = array();
		$lons = array();
		foreach ($labelSeries as $unitid=>$measurement)
		{
			$ts = $measurement[2];
			if ($ts > $maxts || $maxts === false) $maxts = $ts;
			if ($ts < $mints || $mints === false) $mints = $ts;
		}
		
		foreach ($labelSeries as $unitid=>$measurement)
		{
			$ts = $measurement[2];
			if ($minDiff !== false /*&& $unitid != 3*/)
			{
				if ($ts < $maxts - $minDiff) continue;
			}
			
			$coords = $unitCoords[$unitid];
			$lat = $coords[0]; //north/south (Y)
			$lon = $coords[1]; //east/west (X)
			$acc = $coords[2]; //accuracy: either in meters or false (when N/A -> for static units)
			if ($lat < $minlat || $minlat === false) $minlat = $lat;
			if ($lat > $maxlat || $maxlat === false) $maxlat = $lat;
			if ($lon < $minlon || $minlon === false) $minlon = $lon;
			if ($lon > $maxlon || $maxlon === false) $maxlon = $lon;
			$lats[] = $lat;
			$lons[] = $lon;
			
			$set[] = array($lon, $lat, $measurement[3], $measurement[3], $measurement[3], $acc);
		}
		print_r($set);
		
		/*foreach ($firstUnitSeries as $measurement)
		{*/
			// Create new array holding a set of measurements and add the measurement of the first unit
			/*$set = array();
			$unitID = $measurement[0];
			$unitCoord = $unitCoords[$unitID];
			$set[] = array($unitCoord[0], $unitCoord[1], $measurement[3], $measurement[3], $measurement[3]);
			
			// Iterate of the other units
			$timestamp = $measurement[2];
			$minTimestamp = $timestamp;
			$maxTimestamp = $timestamp;
			foreach ($labelSeries as $unitSeries)
			{	
				// Exclude the first unit
				if ($unitSeries !== $firstUnitSeries)
				{
					// Find the measurement of this unit of which the timestamp is closest to that of the first unit
					$minDiff = 120;//false;
					$match = NULL;
					foreach ($unitSeries as $otherMeasurement)
					{	
						$otherTimestamp = $otherMeasurement[2];
						$diff = abs($timestamp - $otherTimestamp);
						if ($diff < $minDiff || !$minDiff)
						{
							$minDiff = $diff;
							$match = $otherMeasurement;
							
							if ($otherTimestamp < $minTimestamp)
							{
								$minTimestamp = $otherTimestamp;
							}
							if ($otherTimestamp > $maxTimestamp)
							{
								$maxTimestamp = $otherTimestamp;
							}
						}
					}
					
					// Add this match to this set
					if ($match !== NULL)
					{
						$unitID = $match[0];
						$unitCoord = $unitCoords[$unitID];
						$set[] = array($unitCoord[0], $unitCoord[1], $match[3], $match[3], $match[3]);
					}
				}
			}*/
			
			//print_r($set);
		
		/*$nr = count($set);
		for ($i=0;$i<$nr;$i++)
		{
			if ($i>1) unset($set[$i]);
		}*/
		
		if (count($set) == 0) continue;
		$nrunits = count($set);
		
		//convert to local coordinate system
		/*(because the algorythm works best (only works?) with a rectangular coordinate system (i.e. 1 in Y is same distance as 1 in X),
		whereas one degree to the north/south is not the same distance as one degree to the west/east when located away from the equator, for example in NL)*/
		$avglat = array_sum($lats) / count($lats); //[deg] north/south (Y)
		$avglon = array_sum($lons) / count($lons); //[deg] east/west (X)
		
		//one degree latitude is 60 nautical miles (definition of nautical mile)
		$meterPerDegreeLat = 60 * 1852; //[m/deg]
		$meterPerDegreeLon = 60 * 1852 * cos(deg2rad($avglat)); //[m/deg]
		
		$maxx = false;
		$minx = false;
		$maxy = false;
		$miny = false;
		foreach ($set as $i=>$val)
		{
			$lon = $val[0];
			$lat = $val[1];
			
			$lat -= $avglat;
			$lon -= $avglon;
			
			$lat = $lat * $meterPerDegreeLat;
			$lon = $lon * $meterPerDegreeLon;
			
			if ($lon < $minx || $minx === false) $minx = $lon;
			if ($lon > $maxx || $maxx === false) $maxx = $lon;
			if ($lat < $miny || $miny === false) $miny = $lat;
			if ($lat > $maxy || $maxy === false) $maxy = $lat;
			
			$set[$i][0] = $lon;
			$set[$i][1] = $lat;
		}
		print_r($set);
		var_dump($minx, $maxx, $miny, $maxy);
		
		//determine the position of the label
		$position = array();
		if (count($set) < 3)
		{
			if (count($set) == 1)
			{
				$lon = $set[0][0];
				$lat = $set[0][1];
				$acc = $set[0][5];
				if ($acc === false) $acc = 0;
				$acc += $labelrange;
				$position = array($lon, $lat, $acc);
			}
			elseif (count($set) == 2)
			{
				
				$rssi1 = floatval($set[0][2]);
				$rssi2 = floatval($set[1][2]);
				
				$abs1 = pow(10, ($rssi1 / 10));
				$reldis1 = round(pow(10, $sig) / $abs1 ^ (1 / $f)) / pow(10, $sig);
				$abs2 = pow(10, ($rssi2 / 10));
				$reldis2 = round(pow(10, $sig) / $abs2 ^ (1 / $f)) / pow(10, $sig);
				
				$fraction = $reldis1 / ($reldis1 + $reldis2);
				
				$lon1 = $set[0][0];
				$lon2 = $set[1][0];
				$lat1 = $set[0][1];
				$lat2 = $set[1][1];
				
				$lat = $lat1 + ($lat2 - $lat1) * $fraction;
				$lon = $lon1 + ($lon2 - $lon1) * $fraction;
				
				$dis = sqrt(pow($lon1 - $lon2, 2) + pow($lat1 - $lat2, 2));
				
				$acc1 = $set[0][5];
				$acc2 = $set[1][5];
				if ($acc1 === false) $acc1 = 0;
				if ($acc2 === false) $acc2 = 0;
				$acc = (($acc1 + $acc2) / 2) + (($factor2 * $dis < $labelrange) ? $factor2 * $dis: $labelrange);
				
				$position = array($lon, $lat, $acc);
			}
			var_dump($position);
		}
		else //only use the 'real' triangulation if there are 3 or more data points
		{
			$pySet = array();
			foreach ($set as $vals)
			{
				$row = array();
				for ($i=0;$i<5;$i++) $row[] = $vals[$i];
				$pySet[] = $row;
			}
			// Add this set to the sets of this label
			//$labelSets[] = $set;
			// or... just do the triangulation already -> that's quicker ;)
			$serialized = serialize($pySet);
			$encoded = urlencode($serialized);
			$position = shell_exec("python triangulate.py $encoded");
			// Now decode the output and add to database
			//var_dump($position);
			$position = str_replace(array("[","]"), "", $position);
			$position = trim($position);
			while (strpos($position, "  ") !== false) $position = str_replace("  ", " ", $position);
			$position = explode(" ", $position);
			
			//normalize accuracy
			if (count($position) == 3)
			{
				$allnumeric = true;
				foreach ($position as $val)
				{
					if (!is_numeric($val))
					{
						$allnumeric = false;
						break;
					}
				}
				if ($allnumeric == false) continue;
				
				$acc = $position[2];
				$refdis = sqrt(pow($maxy - $miny, 2) + pow($maxx - $minx, 2));
				var_dump($acc, $refdis); //, $refacc);
				//$acc = $acc * ($refdis / $refacc);
				$acc = $acc * $refacc;
				var_dump($acc);
				$position[2] = $acc;
			}
			
			var_dump($position);
			//exit();
		}
		
		if (count($position) == 3)
		{
			//convert back to global coordinate system and determine accuracy
			$lon = $position[0]; //[m]
			$lat = $position[1]; //[m]
			$acc = $position[2]; //[m]
			
			$lon = $lon / $meterPerDegreeLon; //[deg]
			$lat = $lat / $meterPerDegreeLat; //[deg]
			
			$lat += $avglat;
			$lon += $avglon;
			
			var_dump($lat, $lon, $acc);
			//continue;
			
			$fields = array($labelID, $maxts, $lat, $lon, $acc, $nrunits);
			$duplCheck = getRow("SELECT * FROM YesDemo_Positions WHERE LabelID=? AND Timestamp=? AND Lat=? AND Lon=? AND Acc=? AND NrUnits=?", $fields);
			if (!$duplCheck)
			{
				$tsduplcheck = getRow("SELECT * FROM YesDemo_Positions WHERE LabelID=? AND Timestamp=?", array($labelID, $maxts));
				if ($tsduplcheck) query("DELETE FROM YesDemo_Positions WHERE LabelID=? AND Timestamp=?", array($labelID, $maxts));
				
				query("INSERT INTO YesDemo_Positions (LabelID, Timestamp, Lat, Lon, Acc, NrUnits) VALUES (?, ?, ?, ?, ?, ?)", $fields);
				print_r($position);
			}
		}
			//echo "\n\n-----\n\n";
		//}
		
		// Add sets for this label to the data sets
		/*if (!isset($dataSets[$labelID]))
		{
			$dataSets[$labelID] = array();
		}
		$dataSets[$labelID] = $labelSets;*/
	//}
}

/* $dataSets is now an associative array with LabelIDs as
** keys and complete data sets as entries in the form:
** array(
** 		array(CoordY, CoordY, RSSI, RSSI, RSII),
** 		array(CoordY, CoordY, RSSI, RSSI, RSII),
**		...
** )*/

/*foreach (

$outputString = serialize($dataSets);
$outputString = urlencode($outputString);
$output = shell_exec("python triangulate.py $outputString");
print $output;

/*
function array_column($array, $column)
{
    $ret = array();
    foreach ($array as $row) $ret[] = $row[$column];
    return $ret;
}*/

SetSetting("nowupdating", 0);
?>