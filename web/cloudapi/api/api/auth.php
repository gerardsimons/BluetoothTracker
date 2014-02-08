<?php
@include_once("../apisub.php"); //to enable code hinting
class APIAuth extends APISub
{
	public $loginreq = array("");
	public $enablecaching = array("");
	public $resetcaching = array("");
	
	//session variable names used for authentication
	private $isloggedin = "authisloggedin";
	private $loginid = "authloginid";
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
	
	//validate the session (check if the user still exists and is active)
	private function validateSession() {
		$interval = $this->up->validatesessioninterval;
		$lastcheck = (isset($this->session["lastvalidate"])) ? $this->session["lastvalidate"]: 0;
		
		//only check if no validation has been performed for a time longer than validatesessioninterval, to reduce server load
		if ($lastcheck < time() - $interval)
		{
			if ($this->session[$this->isloggedin] == true)
			{
				$userid = $this->session[$this->loginid];
				$res = $this->getRow("SELECT * FROM Users WHERE ID=? AND Active=?", array($userid, 1));
				if ($res === false) $this->logout();
			}
			else
				$this->logout();
		}
	}
	
	public function login($loginname, $pass, $machash, $remember = true) {
		//the output for a failed login
		$false = array("result" => false);
		
		//check if user exists and is active
		$res = $this->getRow("SELECT * FROM Users WHERE LoginName=? AND Active=?", array($loginname, 1));
		if ($res === false) return $false;
		
		$userid = $res["ID"];
		$username = $res["Name"];
		
		//match password
		$passhash = $res["Password"];
		$salt = $res["Salt"];
		if ($passhash != $this->hashPass($pass, $salt)) return $false;
		
		//if regtype is other than form, generate new password for security reasons
		$regtype = $res["RegType"];
		if ($regtype != "form") $pass = $this->generateCode();
		
		//generate and save new salt and password hash
		$newsalt = $this->generateCode();
		$newpasshash = $this->hashPass($pass, $newsalt);
		
		$this->query("UPDATE Users SET Password=?, Salt=? WHERE UserID=?", array($newpasshash, $newsalt, $userid));
		
		//continue with login process
		return $this->processLogin($userid, $username, $machash, $remember);
	}
	
	private function hashPass($pass, $salt) {
		return md5($pass.$salt);
	}
	
	public function autoLogin($userid, $loginkey, $hash) {
		//the output for failed login
		$false = array("result" => false);
		
		//check if user exists and is active
		$res = $this->getRow("SELECT * FROM Users WHERE ID=? AND Active=?", array($userid, 1));
		if ($res === false) return $false;
		
		$loginname = $res["LoginName"];
		$username = $res["Name"];
		
		//check if auto-login key exists for user
		$res = $this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=? AND LoginKey=?", array($userid, $loginkey));
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
		$controlhash = md5($loginkey.$this->up->apikey.$machash.$loginname);
		if ($controlhash != $hash) return $false;
		
		//continue with login process
		return $this->processLogin($userid, $username, $machash, true);
	}
	
	//the continuation of the login process, handles the last parts
	private function processLogin($userid, $username, $machash, $remember = true) {
		//generate new auto-login key
		if ($remember == true)
		{
			$loginkey = $this->generateCode();
			
			if ($this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=? AND MACHash=?", array($userid, $machash)))
			{
				$sql = "UPDATE UserAutoLogin SET LoginKey=?, TimestampLastLogin=? WHERE UserID=? AND MACHash=?";
				$fields = array($loginkey, time(), $userid, $machash);
			}
			else
			{
				deleteAutoLogin($userid);
				$sql = "INSERT INTO UserAutoLogin (UserID, LoginKey, MACHash, TimestampFirst, TimestampLastLogin) VALUES (?, ?, ?, ?, ?)";
				$fields = array($userid, $loginkey, $machash, time(), time());
			}
			$this->query($sql, $fields);
		}
		
		//set session variables
		$this->up->resetSession();
		$this->session[$this->isloggedin] = true;
		$this->session[$this->loginid] = $userid;
		$this->session[$this->logints] = time();
		
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
	
	//function to generate a random code of specified length (default 32)
	private function generateCode($length = 32) {
		$code = "";
		$possible = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ0123456789";
		
		for ($i=0;$i<$length;$i++) $code .= $possible[mt_rand(0, strlen($possible)-1)];
		
		return $code;
	}
	
	private function deleteAutoLogin($userid) {
		if ($this->getRow("SELECT * FROM UserAutoLogin WHERE UserID=?", $userid))
		{
			$sql = "DELETE FROM UserAutoLogin WHERE UserID=?";
			$this->query($sql, $userid);
		}
	}
	
	public function logout() {
		//if a user was logged in, make sure no auto-login keys are available for that user
		if ($this->session[$this->loginid] > 0) deleteAutoLogin($this->session[$this->loginid]);
		
		//reset the session login variables
		$this->up->resetSession();
		$this->session[$this->isloggedin] = false;
		$this->session[$this->loginid] = 0;
		$this->session[$this->logints] = false;
		
		return true;
	}
	
	public function register($loginname, $email, $name, $pass, $regtype, $teldata = array(), $addrdata = array()) {
		
	}
}
?>