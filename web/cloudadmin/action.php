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

if ($action == "adduser")
{
	$name = $_POST["name"];
	$email = $_POST["email"];
	$loginname = $_POST["loginname"];
	$regtype = $_POST["regtype"];
	$pass = GenerateCode(8);
	
	$res = $api->call("auth", "register", array($loginname, $email, $name, $pass, $regtype));
	
	if ($res["result"] == true)
	{
		$loginname = $res["loginname"];
		if ($email != "")
		{
			$subject = "whereAt account created for you!";
			$message = "Hi $name!\n\nA whereAt Cloud account just has been created for you:\n\nLogin name: $loginname\nPassword: $pass\n\nUse <a href='$changepassurl'>$changepassurl</a> to change your password.\n\nRegards,\nThe whereAt Cloud team\n\n<i>This message has been automatically generated, do not reply.</i>";
			Email($email, $subject, $message);
		}
	}
	else
		$msg = $res["description"];
}
if ($action == "edituser")
{
	if ($id != "")
	{
		$name = $_POST["name"];
		$email = $_POST["email"];
		$loginname = $_POST["loginname"];
		$regtype = $_POST["regtype"];
		$usertype = $_POST["usertype"];
		$active = $_POST["active"];
		
		query("UPDATE Users SET Name=?, Email=?, LoginName=?, RegType=?, UserType=?, Active=? WHERE ID=?", array($name, $email, $loginname, $regtype, $usertype, $active, $id));
	}
}
if ($action == "deleteuser")
{
	if ($_POST["pass"] == $adminpass)
	{
		if ($id != "")
		{
			query("DELETE FROM Users WHERE ID=?", array($id));
		}
	}
}
if ($action == "addlabel")
{
	$name = $_POST["name"];
	$mac = $_POST["mac"];
	$labeltype = $_POST["labeltype"];
	$owner = $_POST["owner"];
	$active = $_POST["active"];
	$lost = $_POST["lost"];
	$public = $_POST["public"];
	
	if ($owner == "NULL") $owner = NULL;
	
	query("INSERT INTO Labels (Name, MAC, Type, OwnerID, Active, Lost, Public) VALUES (?, ?, ?, ?, ?, ?, ?)", array($name, $mac, $labeltype, $owner, $active, $lost, $public));
}
if ($action == "editlabel")
{
	if ($id != "")
	{
		$name = $_POST["name"];
		$mac = $_POST["mac"];
		$labeltype = $_POST["labeltype"];
		$owner = $_POST["owner"];
		$active = $_POST["active"];
		$lost = $_POST["lost"];
		$public = $_POST["public"];
		
		if ($owner == "NULL") $owner = NULL;
		
		query("UPDATE Labels SET Name=?, MAC=?, Type=?, OwnerID=?, Active=?, Lost=?, Public=? WHERE ID=?", array($name, $mac, $labeltype, $owner, $active, $lost, $public, $id));
	}
}
if ($action == "deletelabel")
{
	if ($_POST["pass"] == $adminpass)
	{
		if ($id != "")
		{
			query("DELETE FROM Labels WHERE ID=?", array($id));
		}
	}
}
if ($action == "addsharing")
{
	if ($id != "")
	{
		$userid = $_POST["userid"];
		if ($userid != "")
		{
			query("INSERT INTO LabelSharing (LabelID, UserID, Timestamp, EndWhenFound) VALUES (?, ?, ?, ?)", array($id, $userid, time(), 0));
		}
	}
}
if ($action == "cancelsharing")
{
	if ($id != "")
	{
		query("DELETE FROM LabelSharing WHERE ID=?", array($id));
	}
}

$_SESSION["actionmsg"] = $msg;
header("Location: /");
?>