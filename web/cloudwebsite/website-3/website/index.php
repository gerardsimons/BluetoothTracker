<?php
session_start();
error_reporting(0);

//URL rewriting and processing
$requri = str_replace(str_replace("index.php", "", str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME'])), "", substr($_SERVER['REQUEST_URI'], 0));
$plusone = false;
if (substr($requri, strlen($requri)-1) == "/")
{
	$requri = substr($requri, 0, strlen($requri)-1);
	$plusone = true;
}
$url = explode("/", $requri);
foreach($url as $i=>$val) $url[$i] = trim($val);
$actions = array();
for ($i=0;$i<10;$i++) $actions[] = "";
if (count($length) > 1)
{
	for ($i=2;$i<count($url);$i++) $actions[] = $urls[$i];
}
$page = $url[0];

//relative main URL
$mainurl = "";
for ($i=1;$i<count($url);$i++) $mainurl .= "../";
if ($plusone == true) $mainurl .= "../";

//page determination
$page = $url[0];
if ($page == "") $page = "home";
if (!file_exists("page.$page.php")) $page = "home";
$file = "page.$page.php";
if (file_exists($file))
{
	ob_start();
	include($file);
	$pagecontent = ob_get_clean();
}
else
	$pagecontent = "";

//render website
include("template.php");
?>