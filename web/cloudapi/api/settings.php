<?php
//this file contains all settings required for the API to function
class APISettings
{
	//service URLs
	public static $websiteurl = "http://www.whereatcloud.com/";
	public static $apiurl = "http://api.whereatcloud.com/";
	
	//database connection information
	public static $dbhost = "localhost";
	public static $dbuser = "isilvestrov_cld";
	public static $dbpass = "F647b2Po";
	public static $dbname = "isilvestrov_cld";
	
	//API and database preparation status
	public static $apiactive = true;
	public static $apimsg = "";
	public static $dbprepenabled = true;
	
	//default API behavior settings
	public static $defaultsettings = array(
		"mainemailverify" => false,
		"addemailverify" => false,
		"telreqpublic" => false,
		"addrreqpublic" => false,
		"telreqlost" => false,
		"addrreqlost" => false,
		"addrcoordsreq" => false,
		"sessiontimeout" => 86400, //1 day
		"sessiontimeoutnoaction" => 1440, //24 minutes (PHP default)
		"autologinexpire" => 31536000, //1 year
		"autologinexpirenoaction" => 2592000, //30 days
		"validatesessioninterval" => 60, //1 minute
		"labelpictureupload" => true,
		"sharedchangename" => false,
		"transferreqtimeout" => 86400, //1 day,
		"usecache" => false
	);
}
?>