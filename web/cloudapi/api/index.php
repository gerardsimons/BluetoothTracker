<?php
//load required files
require_once("api.php");

//no direct access allowed
if (count($_GET) == 0)
{
	header("Location: ".APISettings::$websiteurl);
	exit();
}

//unescape input
if (get_magic_quotes_gpc() == true)
{
	foreach ($_GET as $key=>$val) $_GET[$key] = stripslashes($val);
	foreach ($_POST as $key=>$val) $_POST[$key] = stripslashes($val);
	foreach ($_REQUEST as $key=>$val) $_REQUEST[$key] = stripslashes($val);
	foreach ($_COOKIE as $key=>$val) $_COOKIE[$key] = stripslashes($val);
}

//initialize the API
$apikey = isset($_GET["apikey"]) ? $_GET["apikey"]: "";
$sessionid = isset($_GET["sessionid"]) ? $_GET["sessionid"]: false;
if ($sessionid == false) $sessionid = isset($_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"]: false;
$api = new API($apikey, $sessionid);

//process request
$output = false;
if (isset($_GET["nrfunctions"]))
{
	//if multiple functions are called
	$nrfunctions = $_GET["nrfunctions"];
	if (is_numeric($nrfunctions))
	{
		$results = array();
		for ($i=1;$i<=$nrfunctions;$i++)
		{
			if (isset($_GET["function$i"]))
			{
				$function = explode(".", $_GET["function$i"]);
				if (count($function) == 2)
				{
					$class = $function[0];
					$method = $function[1];
					if ($class != "" && $method != "")
					{
						$input = array();
						if (isset($_REQUEST["input$i"]))
						{
							$input = explode("&", $_GET["input$i"]);
							foreach ($input as $key=>$val) $input[$key] = urldecode($val);
						}
						$results[] = $api->call($class, $method, $input);
					}
					else
						break;
				}
				else
					break;
			}
			else
				break;
		}
		if (count($results) == $nrfunctions) $output = $results;
	}
}
else
{
	//if one function is called
	if (isset($_GET["function"]))
	{
		$function = explode(".", $_GET["function"]);
		if (count($function) == 2)
		{
			$class = $function[0];
			$method = $function[1];
			if ($class != "" && $method != "")
			{
				$input = array();
				if (isset($_GET["input"]))
				{
					$input = explode("&", $_REQUEST["input"]);
					foreach ($input as $key=>$val) $input[$key] = urldecode($val);
				}
				$output = $api->call($class, $method, $input);
			}
		}
	}
}

//if no output was generated, the request is invalid
if ($output === false) $output = $api->throwError(6, "Invalid request.");

//output data
$output = json_encode($output);
echo $output;
?>