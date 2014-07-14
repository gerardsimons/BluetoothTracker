<?php
require_once("settings.php");

$lastVersion = getRow("SELECT Version FROM YesDemo_Version");

if ($lastVersion !== false) {
	echo $lastVersion['Version'];
}
?>