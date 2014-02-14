<?php
//turn off error reporting and load settings
error_reporting(E_ALL);
require_once("settings.php");
require_once("apisub.php");

//this class creates the framework for all sub parts of the API which are located in the api.<class>.php files
//all functionality is enabled and loaded when an instant of this class is created
//NOTE: this class should not be called statically!
class API
{
	public $db = false;
	public $dberror = "";
	public $insertid = 0;
	
	private $cache = false;
	private $cachetimeout = 3600; //sec
	
	public $sessionid = "";
	public $sessprefix = "api";
	
	public $lastaction = false;
	public $sessionstart = false;
	
	public $session = array();
	
	public $apikey = "";
	public $apikeyid = 0;
	public $apikeyactive = false;
	public $apikeymsg = "";
	
	public $apiactive = false;
	public $apimsg = "Not enabled by settings file.";
	
	private $subclasses = array();
	private $reqsubclasses = array("auth");
	private $subclassfolder = "api";
	
	public $txt = array();
	public $lang = "en";
	
	//API initialization: API Key is required and if available session ID
	//Note that for normal PHP sessions (using the PHPSESSID cookie), the sessionid also must be sent, or the local session must already be started using session_start
	public function __construct($apikey, $sessionid = false, $language = false) {
		//check API status
		$this->apiactive = APISettings::$apiactive;
		$this->apimsg = APISettings::$apimsg;
		if ($this->apiactive == false) return;
		
		//setup session
		if (session_id() == "") //if PHP already started a session, then it's fine
		{
			if ($sessionid === false)
				session_start(); //start new session
			else
			{
				//continue session from session ID provided
				session_id($sessionid);
				session_start();
			}
		}
		$this->sessionid = session_id();
		
		//link session variables
		if (!isset($_SESSION[$this->sessprefix])) $_SESSION[$this->sessprefix] = array();
		if (!is_array($_SESSION[$this->sessprefix])) $_SESSION[$this->sessprefix] = array();
		$this->session = $_SESSION[$this->sessprefix];
		$_SESSION[$this->sessprefix] = &$this->session;
		//NOTE: inside this class or a subclass, session variables should be stored/retrieved
		//using the $this->session variable (assoc array) to prevent session variable collision
		//with other frameworks/apps
		
		//save start time
		if (!isset($this->session["sessionstart"])) $this->session["sessionstart"] = time();
		$this->sessionstart = $this->session["sessionstart"];
		
		//load text translations
		if ($language === false) $language = "en";
		$language = (isset($this->session["lang"])) ? $this->session["lang"]: $language;
		$this->txt = $this->loadText("main", $language);
		$this->session["lang"] = $language;
		$this->lang = $language;
		
		//set API key
		$this->apikey = $apikey;
		
		//connect to database
		try {
			$connstr = "mysql:host=".APISettings::$dbhost.";dbname=".APISettings::$dbname.";charset=utf8";
			$this->db = new PDO($connstr, APISettings::$dbuser, APISettings::$dbpass);
		} catch (Expression $e) {
			$this->apiactive = false;
			$this->apimsg = $this->txt["nodbconnection"];
		}
		if ($this->apiactive == false) return;
		
		//load all API subparts
		$files = scandir("./".$this->subclassfolder);
		foreach ($files as $file)
		{
			$filename = $file;
			$file = explode(".", $file);
			if (count($file) == 2)
			{
				if ($file[1] == "php")
				{
					$subname = $file[0];
					$classname = "api$subname";
					include($this->subclassfolder."/".$filename);
					$this->subclasses[$subname] = new $classname($this);
					$this->subclasses[$subname]->txt = $this->loadText($subname, $this->lang);
				}
			}
		}
		
		//check if required subparts are available
		foreach ($this->reqsubclasses as $reqsubclass)
		{
			if (!isset($this->subclasses[$reqsubclass]))
			{
				$this->apiactive = false;
				$this->apimsg = $this->txt["apibroken"];
				break;
			}
		}
		if ($this->apiactive == false) return;
		
		//load default key settings
		foreach (APISettings::$defaultsettings as $var=>$val) $this->$var = $val;
		
		//check if API key is active and load API key specific settings
		$this->apikeyactive = $this->keyIsActive($this->apikey);
		if ($this->apikeyactive == false) return;
		
		//check if session has timed out
		$timeout = false;
		if (isset($this->session["lastaction"])) $this->lastaction = $this->session["lastaction"];
		if ($this->lastaction !== false)
		{
			if ($this->lastaction < time() - $this->sessiontimeoutnoaction) $imeout = true;
		}
		if ($this->sessionstart < time() - $this->sessiontimeout) $timeout = true;
		if ($timeout == true) $this->resetSession();
	}
	
	//function load text translations
	public function loadText($module, $lang) {
		$filepath = "./text/$module.$lang.txt";
		if (file_exists($filepath))
		{
			$lines = file($filepath);
			
			//if the language is other than english, load the default english keys so none is missed in case of outdated translations
			$text = ($lang == "en") ? array(): $this->loadText($module, "en");
			
			//load the keys
			foreach ($lines as $line)
			{
				if (trim($line) != "")
				{
					if (strpos($line, "=") !== false)
					{
						$line = explode("=", $line);
						$key = trim(strtolower($line[0]));
						$val = trim($line[1]);
						$text[$key] = $val;
					}
				}
			}
			
			//make sure it is utf8 encoded
			$check = implode("", array_values($text));
			if (!mb_check_encoding($check, "UTF-8"))
			{
				foreach ($text as $key=>$value) $text[$key] = utf8_encode($value);
			}
			
			return $text;
		}
		else
		{
			//if file is not found, try to load the (default) english translation
			if ($lang != "en")
				return $this->loadText($module, "en");
			else
				return array();
		}
	}
	
	//function to reset the API internal session
	public function resetSession() {
		//preserve the status session vars
		$status = (isset($this->session["status"])) ? $this->session["status"]: false;
		
		$this->session = array();
		$this->session["sessionstart"] = time();
		
		if ($status !== false) $this->session["status"] = $status;
	}
	
	//function wrapper
	public function call($class, $method, $input) {
		$class = strtolower($class);
		$method = strtolower($method);
		
		//check if API is active
		$msg = ($this->apimsg != "") ? ": ".$this->apimsg: ".";
		if ($this->apiactive == false) return $this->throwError(0, "API not active$msg");
		
		//check if API key is active
		$msg = ($this->apikeymsg != "") ? ": ".$this->apikeymsg: ".";
		if ($this->apikeyactive == false) return $this->throwError(1, $this->txt["apikeynotactive"]."$msg");
		
		//check if subpart exists
		if (!isset($this->subclasses[$class])) return $this->throwError(3, $this->txt["class"]." $class ".$this->txt["doesnotexist"]);
		
		//check if function exists
		if (!method_exists($this->subclasses[$class], $method)) return $this->throwError(3, $this->txt["function"]." $class.$method ".$this->txt["doesnotexist"]);
		
		//check if enough input arguments are supplied
		$methodcheck = new ReflectionMethod($this->subclasses[$class], $method);
		$nrargs = $methodcheck->getNumberOfRequiredParameters();
		if (count($input) < $nrargs) return $this->throwError(4, $this->txt["notenoughinput"]);
		
		//set last action time to keep session alive
		$this->lastaction = time();
		$this->session["lastaction"] = $this->lastaction;
		
		//load lists of login/status required functions and caching enabled functions
		$loginreq = $this->subclasses[$class]->loginreq;
		$needstatusrequest = $this->subclasses[$class]->needstatusrequest;
		$enablecaching = $this->subclasses[$class]->enablecaching;
		$resetcaching = $this->subclasses[$class]->resetcaching;
		
		//check if the status must have been requested for function
		$muststatus = (array_search($method, $needstatusrequest) !== false) ? true: false;
		if ($muststatus == true)
		{
			//check if logged in
			if (!isset($this->session["status"])) return $this->throwError(7, $this->txt["nostatusrequest"]);
		}
		
		//check if login is required for function
		$mustlogin = (array_search($method, $loginreq) !== false) ? true: false;
		if ($mustlogin == true)
		{
			//check if logged in
			$loggedin = $this->loggedIn();
			if ($loggedin == false) return $this->throwError(2, $this->txt["mustlogin"]);
		}
		
		//check if the cache should be reset when this function is excecuted
		$resetcache = (array_search($method, $resetcaching) !== false && $this->usecache == true) ? true: false;
		
		//check if caching is enabled for function
		$caching = (array_search($method, $enablecaching) !== false && $this->usecache == true) ? true: false;
		
		//check if output is available in cache
		if ($caching == true && $resetcache == false)
		{
			//load from cache
			$output = $this->getCache($class, $method, $input);
			if ($output !== NULL)
			{
				return array(
					"error" => false,
					"output" => $output
				);
			}
		}
		
		//execute the function
		$output = call_user_func_array(array($this->subclasses[$class], $method), $input);
		
		//we're done if an error was returned
		if (is_array($output))
		{
			if (isset($output["error"]))
			{
				if ($output["error"] == true) return $output;
			}
		}
		
		//reset cache if necessary
		if ($resetcache == true) $this->resetCache();
		
		//cache if enabled
		if ($caching == true) $this->setCache($class, $method, $input, $output);
		
		//output the function results!
		return array(
			"error" => false,
			"output" => $output
		);
	}
	
	//check if API key is active and if active, load API key specific settings
	private function keyIsActive($key, $loadsettings = true) {
		//key cannot be empty
		if ($key == "") return false;
		
		//see if key exists
		$sql = "SELECT * FROM APIKeys WHERE APIKey=?";
		$res = $this->getRow($sql, $key);
		
		//stop if key was not found
		if ($res === false) return false;
		
		$this->apikeyid = $res["ID"];
		
		//set message if available
		if ($res["Message"] !== NULL) $this->apikeymsg = $res["Message"];
		
		//stop if key not active
		if ($res["Active"] != 1) return false;
		
		//load custom settings if applicable
		if ($loadsettings == true)
		{
			if ($res["Settings"] !== NULL)
			{
				$settings = explode(",", $res["Settings"]);
				foreach ($settings as $setting)
				{
					$setting = explode("=", trim($setting));
					if (count($setting) == 2)
					{
						$key = $setting[0];
						$val = $setting[1];
						if (isset(APISettings::$defaultsettings[$key]))
						{
							$defaultval = APISettings::$defaultsettings[$key];
							$defaulttype = gettype($defaultval);
							$type = gettype($val);
							$pass = false;
							//check if the custom setting is of the correct type
							switch ($defaulttype) {
								case "boolean":
									if ($type == "boolean") $pass = true;
									if (is_numeric($type))
									{
										$val = ($val == 1) ? true: false;
										$pass = true;
									}
									break;
								
								case "integer":
								case "double":
									if (is_numeric($type)) $pass = true;
									break;
								
								default:
									if ($defaulttype == $type) $pass = true;
							}
							if ($pass == true) $this->$key = $val;
						}
					}
				}
			}
		}
		
		return true;
	}
	
	//check if logged in
	private function loggedIn() {
		return $this->subclasses["auth"]->isLoggedIn();
	}
	
	//gives the correct error throwing format
	public function throwError($type, $msg) {
		return array(
			"error" => true,
			"errorType" => $type,
			"errorMessage" => $msg,
			"output" => false
		);
	}
	
	//execute a query without expecting response
	public function query($sql, $fields = false) {
		if ($fields === false) $fields = array();
		if (!is_array($fields)) $fields = array($fields);
		$query = $this->db->prepare($sql);
		$res = $query->execute($fields);
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			if ($errorinfo[0] > 0) $this->dberror = $errorinfo[2];
			return false;
		}
		else
		{
			$this->insertid = $this->db->lastInsertId();
			return true;
		}
	}
	
	//execute a query while expecting multiple rows as response
	public function getRows($sql, $fields = false) {
		if ($fields === false) $fields = array();
		if (!is_array($fields)) $fields = array($fields);
		$query = $this->db->prepare($sql);
		$res = $query->execute($fields);
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			if ($errorinfo[0] > 0) $this->dberror = $errorinfo[2];
			return array();
		}
		else
		{
			$rows = array();
			while ($row = $query->fetch(PDO::FETCH_ASSOC)) $rows[] = $row;
			return $rows;
		}
	}
	
	//execute a query while expecting one row as response
	public function getRow($sql, $fields = false) {
		if ($fields === false) $fields = array();
		if (!is_array($fields)) $fields = array($fields);
		if (!strpos($sql, "LIMIT") === false)
			$sql = trim($sql)." LIMIT 1";
		else
		{
			$sql = explode("LIMIT", $sql);
			$sql = trim($sql[0])." LIMIT 1";
		}
		$query = $this->db->prepare($sql);
		$res = $query->execute($fields);
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			if ($errorinfo[0] > 0) $this->dberror = $errorinfo[2];
			return false;
		}
		else
		{
			$row = $query->fetch(PDO::FETCH_ASSOC);
			return $row;
		}
	}
	
	//get data from cache (if NULL is returned: no data is in cache)
	private function getCache($class, $method, $input) {
		if ($this->usecache == true)
		{
			$class = strtolower($class);
			$method = strtolower($method);
			if ($this->cache === false) $this->loadCache();
			foreach ($input as $i=>$inputstr)
			{
				if ($inputstr === false) $inputstr = "f";
				if ($inputstr === true) $inputstr = "t";
				$input[$i] = $inputstr;
			}
			$input = implode(":", $input);
			if (isset($this->cache[$class][$method][$input]))
				return unserialize($this->cache[$class][$method][$input]);
			else
				return NULL;
		}
		else
			return NULL;
	}
	
	//write to cache
	private function setCache($class, $method, $input, $output) {
		global $dbprefix, $con;
		if ($this->usecache == true)
		{
			if ($this->cache === false) $this->loadCache();
			foreach ($input as $i=>$inputstr)
			{
				if ($inputstr === false) $inputstr = "f";
				if ($inputstr === true) $inputstr = "t";
				$input[$i] = $inputstr;
			}
			$input = implode(":", $input);
			$output = serialize($output);
			$class = strtolower($class);
			$method = strtolower($method);
			if (!isset($this->cache[$class])) $this->cache[$class] = array();
			if (!isset($this->cache[$class][$method])) $this->cache[$class][$method] = array();
			$this->cache[$class][$method][$input] = $output;
			//save to DB cache?
			$this->session["cache"] = $this->cache;
			return true;
		}
		else
			return true;
	}
	
	//load the cache from memory (session variable)
	private function loadCache($forcereload = false) {
		$lastreset = $this->getLastResetTime();
		if (is_numeric($lastreset) && $forcereload == false)
		{
			if (isset($this->session["cache"]))
			{
				$lastload = $this->session["lastload"];
				if ($lastload >= $lastreset)
				{
					if ($lastload > time() - $this->cachetimeout)
					{
						$this->cache = $this->session["cache"];
						return true;
					}
				}
			}
		}
		$cache = array();
		//load from DB cache?
		$this->cache = $cache;
		$this->session["cache"] = $cache;
		$this->session["lastload"] = time();
		return true;
	}
	
	//reset the cache
	private function resetCache() {
		//reset DB cache?
		$this->setLastResetTime();
		$this->loadCache(true);
	}
	
	//get cache last reset time
	private function getLastResetTime() {
		if (isset($this->session["lastcachereset"]))
			return $this->session["lastcachereset"];
		else
			return false;
	}
	
	//set cache last reset time
	private function setLastResetTime() {
		$this->session["lastcachereset"] = time();
	}
}
?>