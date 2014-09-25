<?php
require_once("settings.php");

$mac = $_GET["mac"];
WriteLog("getunitid.php request for MAC: $mac");

$unitid = getRow("SELECT ID FROM YesDemo_Units WHERE MAC=?", array($mac));

if ($unitid !== false) {
	WriteLog("MAC found, ID: ".$unitid['ID']."\n");
	echo $unitid['ID'];
}
else
	WriteLog("MAC not found!\n");
?>