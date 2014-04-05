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
		$coordx = $_GET["coordx"];
		$coordy = $_GET["coordy"];
		query("UPDATE YesDemo_Units SET CoordX=?, CoordY=? WHERE ID=?", array($coordx, $coordy, $id));
		exit();
	}
}

$_SESSION["actionmsg"] = $msg;
header("Location: /");
?>