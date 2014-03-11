<?php
session_start();

$contactemail = "info@whereatcloud.com";

$email = $_POST["email"];
$subject = $_POST["subject"];
$emailmsg = $_POST["msg"];
$captcha = $_POST["captcha"];

$_SESSION["contact"] = $_POST;

$msg = "";

if ($captcha == $_SESSION["captchascript"])
{
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
	$headers .= 'From: '.$email;
	if (mail($contactemail, $subject, $emailmsg, $headers))
		$msg = "Thank you for contacting us! We will contact you as soon as possible.";
	else
		$msg = "Unfortunately the script could not send your email. Please click <a href='mailto:$contactemail'>here</a> to try directly.";
	unset($_SESSION["contact"]);
}
else
	$msg = "Captcha not correct, please try again.";

$_SESSION["contactmsg"] = $msg;

header("Location: ".str_replace("contact.php", "", $_SERVER['SCRIPT_NAME'])."contact");
?>