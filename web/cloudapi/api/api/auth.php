<?php
@include_once("../apisub.php"); //to enable code hinting
class APIAuth extends APISub
{
	public $loginreq = array();
	public $needstatusrequest = array("login", "autlogin", "register");
	public $enablecaching = array("getregtypes");
	public $resetcaching = array();
	
	//session variable names used for authentication
	private $isloggedin = "authisloggedin";
	private $loginid = "authloginid";
	private $loginusertype = "authloginusertype";
	private $logints = "authlogintimestamp";
	
	//checks if a user is logged in
	public function isLoggedIn() {
		$this->validateSession();
		return $this->session[$this->isloggedin];
	}
	
	//get the user ID of the user logged in
	public function getUserID() {
		$this->validateSession();
		if ($this->isLoggedIn() == true)
			return $this->session[$this->loginid];
		else
			return 0;
	}
	
	//get the user type of the user logged in
	public function getUserType() {
		$this->validateSession();
		if ($this->isLoggedIn() == true)
			return $this->session[$this->loginusertype];
		else
			return 0;
	}
	
	//validate the session (check if the user still exists and is active)
	private function validateSession() {
		$interval = $this->up->validatesessioninterval;
		$lastcheck = (isset($this->session["lastvalidate"])) ? $this->session["lastvalidate"]: 0;
		
		if (!isset($this->session[$this->isloggedin]))
		{
			$this->resetLoginSessionVars();
		}
		
		//only check if no validation has been performed for a time longer than validatesessioninterval, to reduce server load
		if ($lastcheck < time() - $interval)
		{
			if ($this->session[$this->isloggedin] == true)
			{
				$userid = $this->session[$this->loginid];
				$usertype = $this->session[$this->loginusertype];
				$res = $this->getRow("SELECT * FROM Users WHERE ID=? AND Active=? AND UserType=?", array($userid, 1, $usertype));
				if ($res === false)
					$this->logout();
				else
				{
					if ($this->checkUserType($usertype) == false) $this->logout();
				}
			}
			else
				$this->logout();
		}
	}
	
	public function login($loginname, $pass, $machash, $remember = true) {
		//the output for a failed login
		$false = array("result" => false);
		
		//check if user exists and is active
		$res = $this->getUserbyLoginName($loginname);
		if ($res === false) return $false;
		
		$userid = $res["ID"];
		$username = $res["Name"];
		$usertype = $res["UserType"];
		
		//check if user type exists, is enabled and is accepted for current API key
		if ($this->checkUserType($usertype) == false) return $false;
		
		//check password based on registration type
		$regtype = $res["RegType"];
		if ($regtype == "form")
		{
			//match password
			$passhash = $res["Password"];
			$salt = $res["Salt"];
			if ($passhash != $this->hashPass($pass, $salt)) return $false;
		}
		else
		{
			//get status request time
			$statusts = $this->session["status"]["ts"];
			
			//check if regtype actually is allowed
			if ($this->regTypeAllowed($regtype) == false) return $false;
			
			//get social network user ID, perform some checks to make sure the account is legit
			$networkuserid = explode("_", $loginname);
			if (count($networkuserid) < 2) return $false;
			if ($regtype != $networkuserid[0]) return $false;
			unset($networkuserid[0]);
			$networkuserid = implode("_", array_values($networkuserid)); //to allow for _ in the network user ID
			
			//get and compare the hashes
			if ($pass != $this->hashPassSocialNetworkLogin($regtype, $networkuserid, $statusts)) return $false;
		}
		
		//generate and save new salt and password hash
		$newsalt = $this->generateCode();
		$newpasshash = $this->hashPass($pass, $newsalt);
		
		$this->query("UPDATE Users SET Password=?, Salt=? WHERE UserID=?", array($newpasshash, $newsalt, $userid));
		
		//continue with login process
		return $this->processLogin($userid, $username, $machash, $usertype, $remember);
	}
	
	public function autoLogin($userid, $loginkey, $hash) {
		//the output for failed login
		$false = array("result" => false);
		
		//check if user exists and is active
		$res = $this->getRow("SELECT * FROM Users WHERE ID=? AND Active=?", array($userid, 1));
		if ($res === false) return $false;
		
		$loginname = $res["LoginName"];
		$username = $res["Name"];
		$usertype = $res["UserType"];
		
		//check if user type exists, is enabled and is accepted for current API key
		if ($this->checkUserType($usertype) == false) return $false;
		
		//check if auto-login key exists for user
		$apikeyid = $this->up->apikeyid;
		$res = $this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=? AND LoginKey=? AND APIKeyID=?", array($userid, $loginkey, $apikeyid));
		if ($res === false) return $false;
		
		$machash = $res["MACHash"];
		$tsfirst = $res["TimestampFirst"];
		$tslast = $res["TimestampLastLogin"];
		
		//check if auto-login key is not expired yet
		$autologinexpire = $this->up->autologinexpire;
		$autologinexpirenoaction = $this->up->autologinexpirenoaction;
		
		if (($tsfirst < time() - $autologinexpire) || ($tslast < time() - $autologinexpirenoaction))
		{
			$this->deleteAutoLogin($userid);
			return $false;
		}
		
		//check if control hash matches
		$statusts = $this->session["status"]["ts"];
		
		$controlhash = md5($loginkey.$this->up->apikey.$machash.$loginname.$statusts);
		if ($controlhash != $hash) return $false;
		
		//continue with login process
		return $this->processLogin($userid, $username, $machash, $usertype, true);
	}
	
	//the continuation of the login process, handles the last parts
	private function processLogin($userid, $username, $machash, $usertype, $remember = false) {
		//generate new auto-login key
		if ($remember == true)
		{
			$loginkey = $this->generateCode();
			
			$apikeyid = $this->up->apikeyid;
			if ($this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=? AND MACHash=? AND APIKeyID=?", array($userid, $machash, $apikeyid)))
			{
				$sql = "UPDATE UserAutoLogin SET LoginKey=?, TimestampLastLogin=? WHERE UserID=? AND MACHash=? AND APIKeyID=?";
				$fields = array($loginkey, time(), $userid, $machash);
			}
			else
			{
				$this->deleteAutoLogin($userid);
				$sql = "INSERT INTO UserAutoLogin (UserID, APIKeyID, LoginKey, MACHash, TimestampFirst, TimestampLastLogin) VALUES (?, ?, ?, ?, ?, ?)";
				$fields = array($userid, $apikeyid, $loginkey, $machash, time(), time());
			}
			$this->query($sql, $fields);
		}
		
		//set session variables
		$this->up->resetSession();
		$this->session[$this->isloggedin] = true;
		$this->session[$this->loginid] = $userid;
		$this->session[$this->logints] = time();
		$this->session[$this->loginusertype] = $usertype;
		
		//return data
		$output = array(
			"result" => true,
			"sessionid" => $this->up->sessionid,
			"userid" => $userid,
			"username" => $username
		);
		if ($remember == true) $output["loginkey"] = $loginkey;
		return $output;
	}
	
	//gets list of available user types (names) for API key, returns true if all
	private function getUserTypes() {
		$accepted = trim(strtolower($this->up->acceptedusertypes));
		
		//everything accepted?
		if ($accepted == "all") return true;
		
		return explode(",", $accepted);
	}
	
	//get list of available user type ids for API key, returns true if all
	private function getUserTypeIDs() {
		$usertypes = $this->getUserTypes();
		
		//everything accepted?
		if ($usertypes === true) return true;
		
		$sql = "SELECT * FROM UserTypes WHERE Active=? AND LCASE(Name) IN (".implode(',', array_fill(0, count($usertypes), '?')).")";
		$fields = array(1);
		foreach ($usertypes as $usertype) $fields[] = $usertype;
		
		$res = $this->getRows($sql, $fields);
		if (!$res) return array();
		
		$usertypeids = array();
		foreach ($res as $row) $usertypeids[] = $row["ID"];
		
		return $usertypeids;
	}
	
	//checks if the user type is enabled and allowed for current API key
	private function checkUserType($usertypeid) {
		$res = $this->getRow("SELECT * FROM UserTypes WHERE ID=? AND Active=?", array($usertypeid, 1));
		if (!$res) return false;
		$usertype = strtolower($res["Name"]);
		
		$acceptedusertypes = $this->getUserTypes();
		if ($acceptedusertypes !== true)
		{
			if (array_search($usertype, $acceptedusertypes) === false) return false;
		}
		
		return true;
	}
	
	//cancels all available auto-login keys for current user and API key
	private function deleteAutoLogin($userid) {
		$apikeyid = $this->up->apikeyid;
		if ($this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=? AND APIKeyID=?", array($userid, $apikeyid)))
		{
			$sql = "DELETE FROM UserAutoLogin WHERE UserID=? AND APIKeyID=?";
			$this->query($sql, array($userid, $apikeyid));
		}
	}
	
	//log the user out (if logged in)
	public function logout() {
		//if a user was logged in, make sure no auto-login keys are available for that user
		if ($this->session[$this->loginid] > 0) $this->deleteAutoLogin($this->session[$this->loginid]);
		
		//reset the session login variables
		$this->up->resetSession();
		$this->resetLoginSessionVars();
		
		return true;
	}
	
	//resets the login session variables
	private function resetLoginSessionVars() {
		$this->session[$this->isloggedin] = false;
		$this->session[$this->loginid] = 0;
		$this->session[$this->logints] = false;
		$this->session[$this->loginusertype] = 0;
	}
	
	//get user ID for loginname and user type
	public function getUserbyLoginName($loginname) {
		$usertypes = $this->getUserTypeIDs();
		if ($usertypes === true)
			$res = $this->getRow("SELECT * FROM Users WHERE LoginName=? AND Active=?", array($loginname, 1));
		else
		{
			$sql = "SELECT * FROM Users WHERE LoginName=? AND Active=? AND UserType IN (".implode(',', array_fill(0, count($usertypes), '?')).")";
			$fields = array($loginname, 1);
			foreach ($usertypes as $usertype) $fields[] = $usertype;
			$res = $this->getRow($sql, $fields);
		}
		return $res;
	}
	
	//checks if a user already exists
	public function exists($loginname) {
		$res = $this->getUserbyLoginName($loginname);
		return ($res === false) ? false: true;
	}
	
	//get all available registration types except form
	public function getRegTypes() {
		$regtypes = array();
		$rows = $this->getRows("SELECT * FROM SocialNetworks WHERE Active=? ORDER BY RegType", 1);
		foreach ($rows as $row) $regtypes[] = $row["RegType"];
		return $regtypes;
	}
	
	//check if registration type is allowed
	private function regTypeAllowed($regtype) {
		$res = $this->getRow("SELECT * FROM SocialNetworks WHERE RegType=? AND Active=?", array($regtype, 1));
		return ($res === false) ? false: true;
	}
	
	//register a user
	public function register($loginname, $email, $name, $pass, $regtype, $teldata = "", $addrdata = "") {
		//initialized the failure messages
		$false = array();
		foreach ($this->txt as $key=>$val) $false[$key] = array("result" => false, "description" => $val);
		
		//check if registration is enabled
		if ($this->up->canregister == false) return $false["regnotenabled"];
		
		//get default user type for API key
		$defaulttype = $this->up->defaultusertype;
		if ($defaulttype == "") return $false["couldnotreg"];
		
		//check if user type exists
		$res = $this->getRow("SELECT * FROM UserTypes WHERE LCASE(Name)=? AND Active=?", array(strtolower($defaulttype), 1));
		if (!$res) return $false["couldnotreg"];
		$usertypeid = $res["ID"];
		if ($this->checkUserType($usertypeid) == false) return $false["couldnotreg"];
		
		//check if registration type is allowed
		if ($regtype != "form")
		{
			if ($this->regTypeAllowed($regtype) == false) return $false["noregtype"];
		}
		
		//make sure loginname is email address if regtype = form and check if email address is valid
		if ($regtype == "form")
		{
			if ($email == "") return $false["emailnotvalid"];
			if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) return $false["emailnotvalid"];
			$loginname = $email;
		}
		elseif ($email != "")
		{
			if (filter_var($email, FILTER_VALIDATE_EMAIL) == false) return $false["emailnotvalid"];
		}
		
		//validate the loginname if regtype != form
		if ($regtype != "form")
		{
			$networkuserid = explode("_", $loginname);
			if (count($networkuserid) < 2) return $false["couldnotlogin"];
			if ($regtype != $networkuserid[0]) return $false["couldnotlogin"];
			unset($networkuserid[0]);
			$networkuserid = implode("_", array_values($networkuserid));
		}
		
		//check if user already exists
		if ($this->exists($loginname) == true) return $false["alreadyexists"];
		
		//real name is required
		if ($name == "") return $false["noname"];
		
		//check if other user with same email address already exists
		if ($email != "")
		{
			if ($this->getRow("SELECT * FROM Users WHERE Email=? AND UserType=?", array($email, $usertype))) return $false["emailexists"];
		}
		
		//validate password if regtype != form
		if ($regtype != "form")
		{
			$statusts = $this->session["status"]["ts"];
			if ($pass != $this->hashPassSocialNetworkLogin($regtype, $networkuserid, $statusts)) return $false["couldnotlogin"];
		}
		
		//register the user!
		if ($regtype == "form")
		{
			//hash the password
			$salt = $this->generateCode();
			$passhash = $this->hashPass($pass, $salt);
			
			$sql = "INSERT INTO Users (Name, Email, LoginName, Password, Salt, UserType, RegType, Active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$fields = array($name, $email, $loginname, $pass, $salt, $usertype, $regtype, 1);
		}
		elseif ($email != "")
		{
			$sql = "INSERT INTO Users (Name, Email, LoginName, UserType, RegType, Active) VALUES (?, ?, ?, ?, ?, ?)";
			$fields = array($name, $email, $loginname, $usertype, $regtype, 1);
		}
		else
		{
			$sql = "INSERT INTO Users (Name, LoginName, UserType, RegType, Active) VALUES (?, ?, ?, ?, ?)";
			$fields = array($name, $loginname, $usertype, $regtype, 1);
		}
		if ($this->query($sql, $fields) == false) return $false["couldnotreg"];
		
		//success!
		$userid = $this->insertid;
		
		//do stuff with the telephone numbers and the addresses
		
		return array(
			"result" => true,
			"loginname" => $loginname,
			"userid" => $userid
		);
	}
	
	//function to generate a random code of specified length (default 32)
	private function generateCode($length = 32) {
		$code = "";
		$possible = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789";
		
		for ($i=0;$i<$length;$i++) $code .= $possible[mt_rand(0, strlen($possible)-1)];
		
		return $code;
	}
	
	//password hashing functions
	public function hashPass($pass, $salt) {
		return md5($pass.$salt);
	}
	public function hashPassSocialNetworkLogin($regtype, $networkuserid, $statusts) {
		return substr(md5($regtype.$networkuserid.$statusts), $statusts % 21);
	}
}
?>