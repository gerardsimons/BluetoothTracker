<?php
require_once("settings.php");

$res = $api->call("label", "getlabels", array());

$labels = array();
if (!isset($res["error"]))
{
	foreach ($res as $row)
	{
		$id = $row["id"];
		$mac = $row["mac"];
		$labels[$id] = $mac;
	}
}

echo json_encode($labels);
?>