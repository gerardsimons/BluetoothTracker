<?php
require_once("apidb.php");
$res = APIDBStructure::prepareDatabase();
if ($res == false)
	echo str_replace("\n", "<br />", APIDBStructure::$msg);
else
	echo "Ok!";
?>