<?php
require_once("settings.php");

$mac = $_GET["mac"];

$unitid = getRow("SELECT ID FROM YesDemo_Units WHERE MAC=?", array($mac));

if ($unitid !== false) {
	echo $unitid['ID'];
}
?>