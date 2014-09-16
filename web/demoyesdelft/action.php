<?php
require_once("settings.php");
error_reporting(E_ALL);
ini_set("display_errors", 1);

if ($_SESSION["loggedin"] != true)
{
	header("Location: /");
	exit();
}

$action = $_GET["action"];
$id = $_GET["id"];

$msg = "";

if ($action == "setcoordunit")
{
	if ($id != "")
	{
		$lat = $_GET["lat"];
		$lon = $_GET["lon"];
		query("UPDATE YesDemo_Units SET Lat=?, Lon=? WHERE ID=?", array($lat, $lon, $id));
		exit();
	}
}

$_SESSION["actionmsg"] = $msg;
header("Location: /");
?>