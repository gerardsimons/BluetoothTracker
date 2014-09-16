<?php
require_once("settings.php");

header("Content-Type: text/javascript");

$tsrange = 4; //sec
$postime = 15; //sec

$fromts = $_GET["fromts"];
if (!is_numeric($fromts) || $fromts == "") $fromts = 0;

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

$labeldata = array();
//$res = getRows("SELECT p.* FROM (SELECT * FROM YesDemo_Positions ORDER BY ID DESC) AS p GROUP BY p.LabelID", array()); //select latest positions
$res = getRows("SELECT o.LabelID, IF(p.NrUnits=1,p.Lat,AVG(o.Lat)) AS Lat, IF(p.NrUnits=1,p.Lon,AVG(o.Lon)) AS Lon, IF(p.NrUnits=1,p.Acc,AVG(o.Acc)) AS Acc, p.Timestamp FROM (SELECT p.* FROM (SELECT * FROM YesDemo_Positions ORDER BY ID DESC) AS p GROUP BY p.LabelID) AS p JOIN YesDemo_Positions AS o ON o.LabelID=p.LabelID WHERE o.Timestamp>=p.Timestamp-$postime GROUP BY o.LabelID", array()); //select average of last x seconds of position reports per label
foreach ($res as $row)
{
	$ago = microtime(true) - $row["Timestamp"];
	$labeldata[$row["LabelID"]] = array($row["Lat"], $row["Lon"], $row["Acc"], $ago);
}

$lastupdates = array();
$res = getRows("SELECT UnitID, MAX(Timestamp) AS LastTime FROM YesDemo_Tracking GROUP BY UnitID");
foreach ($res as $row) $lastupdates[$row["UnitID"]] = $row["LastTime"];

$units = array();
$res = getRows("SELECT * FROM YesDemo_Units WHERE Hide<>1 OR Hide IS NULL", array());
foreach ($res as $row)
{
	$id = $row["ID"];
	$ago = (isset($lastupdates[$id])) ? time() - $lastupdates[$id]: 99999999;
	$units[] = $row["ID"].": [".$row["Lat"].", ".$row["Lon"].", $ago]";
}
$jsunitdata = "var unitsupdate = {".implode(", ", $units)."};\n";

$jslabeldata = array();
foreach ($labeldata as $labelid=>$data) $jslabeldata[] = "$labelid: [".implode(", ", $data)."]";
$jslabeldata = "var labelsupdate = {".implode(", ", $jslabeldata)."};\n";

echo $jslabeldata;
echo $jsunitdata;
?>
for (var id in labelsupdate) {
	for (var i in labelsupdate[id]) {
		labels[id][i] = labelsupdate[id][i];
    }
}
for (var id in unitsupdate) {
	for (var i in unitsupdate[id]) {
		units[id][i] = unitsupdate[id][i];
    }
}