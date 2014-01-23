<?php
session_start();

$font = dirname(__FILE__)."/calibri.ttf";
$height = 50;
$width = 200;

function GenerateCode($length = 32){
	$string = "";
	$possible = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789";
	
	for($i=0;$i < $length;$i++) {
    	$char = $possible[mt_rand(0, strlen($possible)-1)];
	    $string .= $char;
	}
	
	return $string;
}

$code = GenerateCode(6);

$fontsize = round($height * 0.7);
$image = imagecreate($width, $height);
$backgroundcolor = imagecolorallocate($image, 255, 255, 255);
$noisecolor = imagecolorallocate($image, 20, 40, 100);

for($i=0;$i<(($width * $height)/4);$i++) {
	imageellipse($image, mt_rand(0,$width), mt_rand(0,$height), 1, 1, $noisecolor);
}
$textcolor = imagecolorallocate($image, 20, 40, 100);
imagettftext($image, $fontsize, 0, 25, 40, $textcolor, $font ,$code);

header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
$_SESSION["captchascript"] = $code;
?>