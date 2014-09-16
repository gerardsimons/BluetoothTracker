<?php
session_start();

$pathtoapi = "../../api.whereatcloud.com/";

$dbhost = "localhost";
$dbuser = "isilvestrov_cld";
$dbpass = "F647b2Po";
$dbname = "isilvestrov_cld";

$adminuser = "whereAt";
$adminpass = "Industries";

try {
	$connstr = "mysql:host=".$dbhost.";dbname=".$dbname.";charset=utf8";
	$db = new PDO($connstr, $dbuser, $dbpass);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
	echo "No database connection.";
	exit();
}
$insertid = 0;
$dberror = "";

require_once($pathtoapi."api.php");
$apikey = "jrZ5H2mdbf8LXB41Uj47ccad";
$api = new API($apikey, session_id());
$res = $api->call("auth", "isloggedin", array());
if ($res != true)
{
	$res = $api->call("status", "status", array());
	if (isset($res["error"]) && $res["error"] == true)
	{
		echo "API error occured: ".$res["errorMessage"]." (".$res["errorType"].")";
		exit();
	}
	$res = $api->call("auth", "login", array("yesdelftdemo@whereatcloud.com", "yesDelftDemo", "", false));
	if ($res["result"] != true)
	{
		echo "Could not log in to API!";
		exit();
	}
}

error_reporting(0);

//database query
function query($sql, $fields = false) {
	global $db, $insertid, $dberror;
	if ($fields === false) $fields = array();
	if (!is_array($fields)) $fields = array($fields);
	try {
		$query = $db->prepare($sql);
		return $query->execute($fields);
	} catch (Exception $e) {
		$dberror = $e->getMessage();
		return false;
	}
}

//database query met 1 of meerdere rijen als output
function getRows($sql, $fields = false) {
	global $db, $insertid, $dberror;
	if ($fields === false) $fields = array();
	if (!is_array($fields)) $fields = array($fields);
	try {
		$query = $db->prepare($sql);
		$res = $query->execute($fields);
		if ($res === false) return false;
		$rows = array();
		while ($row = $query->fetch(PDO::FETCH_ASSOC)) $rows[] = $row;
		return $rows;
	} catch (Exception $e) {
		$dberror = $e->getMessage();
		return array();
	}
}

//database query met 1 rij als output
function getRow($sql, $fields = false) {
	global $db, $insertid, $dberror;
	if ($fields === false) $fields = array();
	if (!is_array($fields)) $fields = array($fields);
	if (!strpos($sql, "LIMIT") === false)
		$sql = trim($sql)." LIMIT 1";
	else
	{
		$sql = explode("LIMIT", $sql);
		$sql = trim($sql[0])." LIMIT 1";
	}
	try {
		$query = $db->prepare($sql);
		$res = $query->execute($fields);
		if ($res === false) return false;
		$row = $query->fetch(PDO::FETCH_ASSOC);
		return $row;
	} catch (Exception $e) {
		$dberror = $e->getMessage();
		return false;
	}
}

function GetSetting($var) {
	$res = getRow("SELECT * FROM YesDemo_Settings WHERE Var=?", $var);
	if ($res === false) return false;
	return $res["Val"];
}

function SetSetting($var, $val) {
	if (getRow("SELECT * FROM YesDemo_Settings WHERE Var=?", $var))
		return query("UPDATE YesDemo_Settings SET Val=? WHERE Var=?", array($val, $var));
	else
		return query("INSERT INTO YesDemo_Settings (Var, Val) VALUES (?, ?)", array($var, $val));
}

function GenerateCode($length = 32) {
	$code = "";
	$possible = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789";
	
	for ($i=0;$i<$length;$i++) $code .= $possible[mt_rand(0, strlen($possible)-1)];
	
	return $code;
}

function Email($to, $subject, $message) {
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= 'From: <noreply@whereatcloud.com>' . "\r\n";
	
	$message = str_replace("\n", "<br />", $message);
	
	mail($to, $subject, $message, $headers);
}
?>