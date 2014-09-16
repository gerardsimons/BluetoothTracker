<?php
//$to = "jasper.bussemaker@gmail.com";
$to = "jasper@wheratcloud.com";
$subject = "PHP mail testing page";
$content = "Hi Jasper,\n\nDit is een test-email!\nLaten we hopen dat die VPS van transip dit toelaat!\n\nGroetjes,\nJasper\n(beetje schitzofreen dit..)";
$headers = "From: Jasper Bussemaker <jasper@whereatcloud.com>\r\n";

error_reporting(E_ALL);
ini_set("display_errors", 1);

ini_set("SMTP", "ssl://smtp.google.com");
ini_set("smtp_port", "465");
//phpinfo();
//exit();

$res = mail($to, $subject, $content, $headers);
var_dump($res);
?>