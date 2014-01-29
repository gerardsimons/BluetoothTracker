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
	private $usecache = false;																												//turn on for live!
	private $cachetimeout = 3600; //sec
	
	public $sessionid = "";
	public $sessprefix = "api";
	
	public $sessiontimeout = 86400; //sec
	public $sessiontimeoutnoaction = 3600; //sec
	public $lastaction = false;
	public $sessionstart = false;
	
	public $session = array();
	
	public $apikey = "";
	public $apikeyactive = false;
	public $apikeymsg = "";
	
	public $apiactive = true;
	public $apimsg = "";
	
	private $subclasses = array();
	private $reqsubclasses = array("auth", "apikey");
	private $subclassfolder = "api";
	
	public function __construct($apikey, $sessionid = false) {
		if ($this->apiactive == false) return;
		
		//setup session
		if (session_id() == "") //if PHP already started a session, then it's fine
		{
			if (isset($_COOKIE["PHPSESSID"]))
				session_start(); //cookies enabled system!
			else
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
		
		//check if session has timed out
		$timeout = false;
		if (isset($this->session["lastaction"])) $this->lastaction = $this->session["lastaction"];
		if ($this->lastaction !== false)
		{
			if ($this->lastaction < time() - $this->sessiontimeoutnoaction) $imeout = true;
		}
		if ($this->sessionstart < time() - $this->sessiontimeout) $timeout = true;
		if ($timeout == true) $this->resetSession();
		
		//set API key
		$this->apikey = $apikey;
		
		//connect to database
		$connstr = "mysql:host=".APISettings::$dbhost.";dbname=".APISettings::$dbname.";charset=utf8";
		$this->db = new PDO($connstr, APISettings::$dbuser, APISettings::$dbpass);
		
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
				}
			}
		}
		
		//check if required subparts are available
		foreach ($this->reqsubclasses as $reqsubclass)
		{
			if (!isset($this->subclasses[$reqsubclass]))
			{
				$this->apiactive = false;
				$this->apimsg = "API broken.";
				break;
			}
		}
		
		//only continue starting up if the API is actually active
		if ($this->apiactive == false) return;
		
		//check if API key is active
		$this->apikeyactive = true;
		$this->apikeyactive = $this->regularCall("apikey", "isactive", array($this->apikey));
	}
	
	//function to reset the API internal session
	public function resetSession() {
		$this->session = array();
		$this->session["sessionstart"] = time();
	}
	
	//function to just get the output of the function without the error results
	public function regularCall($class, $method, $input) {
		$output = $this->call($class, $method, $input);
		return $output["output"];
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
		if ($this->apikeyactive == false) return $this->throwError(1, "API key not active$msg");
		
		//check if subpart exists
		if (!isset($this->subclasses[$class])) return $this->throwError(3, "Class $class does not exist.");
		
		//check if function exists
		if (!method_exists($this->subclasses[$class], $method)) return $this->throwError(3, "Function $method does not exist.");
		
		//check if enough input arguments are supplied
		$methodcheck = new ReflectionMethod($this->subclasses[$class], $method);
		$nrargs = $methodcheck->getNumberOfRequiredParameters();
		if (count($input) < $nrargs) return $this->throwError(4, "Not enough input arguments.");
		
		//set last action time to keep session alive
		$this->lastaction = time();
		$this->session["lastaction"] = $this->lastaction;
		
		//load lists of login required functions and caching enabled functions
		$loginreq = $this->subclasses[$class]->loginreq;
		$enablecaching = $this->subclasses[$class]->enablecaching;
		$resetcaching = $this->subclasses[$class]->resetcaching;
		
		//check if login is required for function
		$mustlogin = (array_search($method, $loginreq) !== false) ? true: false;
		if ($mustlogin == true)
		{
			//check if logged in
			$loggedin = $this->regularCall("auth", "isloggedin", array());
			if ($loggedin == false) return $this->throwError(2, "Must be logged in to access this function.");
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
	public function query($sql) {
		$res = $this->db->exec($sql);
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			$this->dberror = $errorinfo[2];
			return false;
		}
		else
		{
			$this->insertid = $this->db->lastInsertId();
			return true;
		}
	}
	
	//execute a query while expecting multiple rows as response
	public function getRows($sql) {
		$query = $this->db->prepare($sql);
		$res = $query->execute();
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			$this->dberror = $errorinfo[2];
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
	public function getRow($sql) {
		if (!strpos($sql, "LIMIT") === false)
			$sql = trim($sql)." LIMIT 1";
		else
		{
			$sql = explode("LIMIT", $sql);
			$sql = trim($sql[0])." LIMIT 1";
		}
		$query = $this->db->prepare($sql);
		$res = $query->execute();
		if ($res === false)
		{
			$errorinfo = $this->db->errorInfo();
			$this->dberror = $errorinfo[2];
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
				$input[$i] = addslashes($inputstr);
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
				$input[$i] = addslashes($inputstr);
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