<?php
header('Content-Type: text/plain; charset=utf-8');

require_once("api.php");

$api = new API($_GET["apikey"], (isset($_GET["sessionid"])?$_GET["sessionid"]:false));

$class = "status";
$function = "status";

$res = $api->call($class, $function, array(1, 2));
if ($res["error"] == true)
	print_r($res);
else
	print_r($res["output"]);
?>