<?php
/* This script does two things:
** 1.	It finds sets of measurements of approximately the same time and groups them
** 2.	It takes these sets of measurement data and calculates the corresponding position
*/
require_once("settings.php");

header("Content-Type: text/plain");
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Get measurements from database
$data = getRows("SELECT * FROM YesDemo_Tracking");

// Group the data in measurement series per label and per unit
$measurements = array();
foreach ($data as $measurement)
{
	$labelID = $measurement["LabelID"];
	$unitID  = $measurement["UnitID"];
	if (!isset($measurements[$labelID][$unitID]))
	{
		$measurements[$labelID][$unitID] = array();
	}
	$measurements[$labelID][$unitID][] = array
	(
		$measurement["UnitID"],
		$measurement["LabelID"],
		$measurement["Timestamp"],
		$measurement["SignalStrength"]
	);
}

// Get associative array with UnitIDs as keys and their X and Y coordinates as values
$units = getRows("SELECT ID, CoordX, CoordY FROM YesDemo_Units");
$unitCoords = array();
foreach ($units as $unit)
{
	$unitID = $unit["ID"];
	$unitCoords[$unitID] = array($unit["CoordX"], $unit["CoordY"]);
}

$labelIDs = array_column(getRows("SELECT ID FROM Labels"), "ID");
// Populate the datasets
$dataSets = array();
foreach ($labelIDs as $labelID)
{
	if (isset($measurements[$labelID]))
	{
		$labelSeries = $measurements[$labelID];
	
		// Mark the first unit in the data as reference unit
		reset($labelSeries);
		$firstUnitSeries = current($labelSeries);
		
		$labelSets = array();
		foreach ($firstUnitSeries as $measurement)
		{
			// Create new array holding a set of measurements and add the measurement of the first unit
			$set = array();
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
					$minDiff = false;
					$match = null;
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
					$unitID = $match[0];
					$unitCoord = $unitCoords[$unitID];
					$set[] = array($unitCoord[0], $unitCoord[1], $match[3], $match[3], $match[3]);
				}
			}
			
			// Add this set to the sets of this label
			//$labelSets[] = $set;
			// or... just do the triangulation already -> that's quicker ;)
			$serialized = serialize($set);
			$encoded = urlencode($serialized);
			$position = shell_exec("python triangulate.py $encoded");
			// Now decode the output and add to database
			$position = str_replace(array("[","]"), "", $position);
			$position = trim($position);
			$position = explode("  ", $position);
			
			if (count($position) == 2)
			{
				$avgTimestamp = ($maxTimestamp + $minTimestamp) / 2;
				
				$duplCheck = getRow("SELECT LabelID, Timestamp, CoordX, CoordY FROM YesDemo_Positions WHERE LabelID=$labelID AND Timestamp=$avgTimestamp AND CoordX=$position[0] AND CoordY=$position[1]");
				if (count($duplCheck) === 1)
				{
					query("INSERT INTO YesDemo_Positions (LabelID, Timestamp, CoordX, CoordY) VALUES (?, ?, ?, ?)", array($labelID, $avgTimestamp, $position[0], $position[1]));
					print_r($position);
				}
			}
		}
		
		// Add sets for this label to the data sets
		/*if (!isset($dataSets[$labelID]))
		{
			$dataSets[$labelID] = array();
		}
		$dataSets[$labelID] = $labelSets;*/
	}
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

?>