<?php
require_once("settings.php");

$start = microtime(true);

$unitid = $_GET["unitid"];
$gapikey = $_GET["apikey"];
$labelids = $_GET["labelids"];
$signals = $_GET["signals"];

if (isset($_GET["lat"]))
{
	$lat = $_GET["lat"];
	$lon = $_GET["lon"];
	$acc = $_GET["acc"];
}
else
{
	$lat = NULL;
	$lon = NULL;
	$acc = NULL;
}

//if ($gapikey != $apikey) exit();

$row = getRow("SELECT * FROM YesDemo_Units WHERE ID=?", array($unitid));
if (!$row) exit();

$calibration = $row["Calibration"];
if (!is_numeric($calibration) || $calibration === NULL) $calibration = 0;

if (!is_array($labelids)) $labelids = array($labelids);
if (!is_array($signals)) $signals = array($signals);

$data = array();
foreach ($labelids as $i=>$labelid)
{
	$signal = $signals[$i];
	$signal += $calibration;
	$data[$labelid] = $signal;
}

$res = $api->call("label", "getlabels", array());

$labels = array();
if (!isset($res["error"]))
{
	foreach ($res as $row)
	{
		$id = $row["id"];
		$labels[] = $id;
	}
}

if ($lat !== NULL)
{
	query("UPDATE YesDemo_Units SET Lat=?, Lon=?, Acc=? WHERE ID=?", array($lat, $lon, $acc, $unitid));
}

$ts = microtime(true);

foreach ($labels as $labelid)
{
	if (isset($data[$labelid]))
	{
		$signal = $data[$labelid];
		query("INSERT INTO YesDemo_Tracking (UnitID, LabelID, Timestamp, SignalStrength, Lat, Lon, Acc) VALUES (?, ?, ?, ?, ?, ?, ?)", array($unitid, $labelid, $ts, $signal, $lat, $lon, $acc));
	}
}

$end = microtime(true);
$diff = ($end - $start) * 1000;

echo "\n\nExecution time: $diff ms";
?>