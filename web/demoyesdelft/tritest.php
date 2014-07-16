<?php
require_once("settings.php");

$tmethod = $_GET["t"];
if (!is_numeric($tmethod)) $tmethod = 1;
$filename = "triangulation$tmethod.php";
if (!file_exists($filename)) $filename = "triangulation1.php";
require_once($filename);

header("Content-Type: text/plain");

error_reporting(E_ALL);
ini_set("display_errors", 1);

$data = array(
	array(0, 0, -30, -31, -29),
	array(0, 100, -30, -31, -29),
	array(100, 0, -30, -31, -29),
	array(100, 100, -30, -31, -29)
);

$start = microtime(true);
print_r(Triangulate($data));
$end = microtime(true);
$diff = ($end - $start) * 1000;
echo "\n\nExecution time: $diff ms";
exit();

$data = ConvertToRelativeDistance($data);
print_r(DoTriangulate(array($data[1], $data[2], $data[0])));
?>