<?php
require_once("settings.php");

$tmethod = $_GET["tmethod"];
if (!is_numeric($tmethod)) $tmethod = 6;
$filename = "triangulation$tmethod.php";
if (!file_exists($filename)) $filename = "triangulation1.php";
require_once($filename);

header("Content-Type: text/javascript");

$tsrange = 4; //sec

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

$units = array();
$res = getRows("SELECT * FROM YesDemo_Units", array());
foreach ($res as $row) $units[$row["ID"]] = array($row["CoordX"], $row["CoordY"]);

$highestts = false;
$signaldata = array();
foreach ($labels as $labelid)
{
	$row = getRow("SELECT * FROM YesDemo_Tracking WHERE LabelID=? AND Timestamp>? ORDER BY Timestamp DESC", array($labelid, $fromts));
	if ($row !== false)
	{
		$lastts = $row["Timestamp"];
		$mints = $lastts - $tsrange;
		if ($lastts > $higestts) $highestts = $lastts;
		
		$data = array();
		$res = getRows("SELECT AVG(SignalStrength) AS SignalStrength, MIN(SignalStrength) AS MinSignal, MAX(SignalStrength) AS MaxSignal, UnitID FROM YesDemo_Tracking WHERE LabelID=? AND Timestamp>=? GROUP BY UnitID", array($labelid, $mints));
		foreach ($res as $row)
		{
			$unitid = $row["UnitID"];
			$signal = $row["SignalStrength"];
			$max = $row["MaxSignal"];
			$min = $row["MinSignal"];
			if (isset($units[$unitid]))
			{
				$unitx = $units[$unitid][0];
				$unity = $units[$unitid][1];
				$data[] = array($unitx, $unity, $signal, $min, $max);
			}
		}
		if (count($data) >= 3) $signaldata[$labelid] = $data;
	}
}

//fake data
//foreach ($labels as $labelid) $signaldata[$labelid] = array();

$labeldata = array();
foreach ($signaldata as $labelid=>$data)
{
	//$coords = TriangulateFake($data);
	ob_start();
	$start = microtime(true);
	$coords = Triangulate($data);
	$diff = (microtime(true) - $start) * 1000;
	echo "Search time: $diff ms\n";
	$output = ob_get_clean();
	if (isset($_GET["echo"])) echo $output;
	if ($coords !== false)
	{
		$labeldata[$labelid] = $coords;
	}
}

function TriangulateFake($data) {
	$x = (rand() / getrandmax()) * 100;
	$y = (rand() / getrandmax()) * 100;
	$acc = (rand() / getrandmax()) * 10;
	return array($x, $y, $acc);
}

$jslabeldata = array();
foreach ($labeldata as $labelid=>$data) $jslabeldata[] = "$labelid: [".implode(", ", $data)."]";
$jslabeldata = "var labelsupdate = {".implode(", ", $jslabeldata)."};\n";

echo $jslabeldata;
?>
for (var id in labelsupdate) {
	labels[id] = labelsupdate[id];
}
<?php if ($highestts !== false) { ?>fromts = <?php echo $highestts; ?>;<?php } ?>