<?php
require_once("settings.php");

$unitid = $_GET["unitid"];
$gapikey = $_GET["apikey"];
$labelids = $_GET["labelids"];
$signals = $_GET["signals"];

//if ($gapikey != $apikey) exit();

if (!getRow("SELECT * FROM YesDemo_Units WHERE ID=?", array($unitid))) exit();

if (!is_array($labelids)) $labelids = array($labelids);
if (!is_array($signals)) $signals = array($signals);

$data = array();
foreach ($labelids as $i=>$labelid)
{
	$signal = $signals[$i];
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

$ts = microtime(true);

foreach ($labels as $labelid)
{
	if (isset($data[$labelid]))
	{
		$signal = $data[$labelid];
		query("INSERT INTO YesDemo_Tracking (UnitID, LabelID, Timestamp, SignalStrength) VALUES (?, ?, ?, ?)", array($unitid, $labelid, $ts, $signal));
	}
}
?>