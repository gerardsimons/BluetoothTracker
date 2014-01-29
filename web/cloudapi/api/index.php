<?php
//load the API
require_once("api.php");

$api = new API($_GET["apikey"], (isset($_GET["sessionid"])?$_GET["sessionid"]:false));

$class = "status";
$function = "status";

$res = $api->call($class, $function, array(1, 2));
if ($res["error"] == true)
	var_dump($res);
else
	var_dump($res["output"]);
?>