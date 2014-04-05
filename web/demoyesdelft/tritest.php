<?php
require_once("settings.php");
require_once("triangulation4.php");

header("Content-Type: text/plain");

error_reporting(E_ALL);
ini_set("display_errors", 1);

$data = array(
	array(0, 0, -30, -31, -29),
	array(0, 100, -30, -31, -29),
	array(100, 0, -30, -31, -29),
	array(100, 100, -30, -31, -29)
);

print_r(Triangulate($data));
exit();

$data = ConvertToRelativeDistance($data);
print_r(DoTriangulate(array($data[1], $data[2], $data[0])));
?>